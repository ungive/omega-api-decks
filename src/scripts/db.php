<?php

namespace Db;

use Config;

use function Utility\download_file;
use function Utility\temp_filename;


const SEPARATOR = ',';
const PLACEHOLDER = '?';

function insert_chunked(\PDO $db, string $table,
                        array $columns, array $rows,
                        int $chunk_size = 16)
    : bool
{
    if (count($rows) === 0)
        return true;

    $chunks = array_chunk($rows, $chunk_size);
    $last_chunk = array_splice($chunks, count($chunks) - 1, 1);

    $column_names = implode(SEPARATOR, $columns);
    $row_placeholders = implode(SEPARATOR, array_fill(0, count($columns), PLACEHOLDER));
    $base_query = "INSERT INTO $table ($column_names) VALUES";

insert:
    $placeholders = implode(SEPARATOR, array_fill(0, $chunk_size, "($row_placeholders)"));
    $statement = $db->prepare("$base_query $placeholders;");

    foreach ($chunks as $chunk) {
        $values = array_merge(...$chunk);
        $retval = $statement->execute($values);
        if (!$retval)
            return false;
    }

    // last chunk
    if (count($last_chunk) > 0) {
        $chunk_size = count($last_chunk[0]);
        $chunks = array_splice($last_chunk, 0);
        $last_chunk = [];
        goto insert;
    }

    return true;
}

function sanitize_name(string $name): string
{
    $name = strtolower($name);
    $name = preg_replace("/(\s*[^a-z0-9]\s*)+/", " ", $name);
    $name = trim($name);
    return $name;
}

/**
* returns the first two identifying characters of a name.
* @param string $s the name in its sanitized form
*/
function name_cluster(string $s, string $placeholder = '_'): string
{
    switch (strlen($s)) {
    default: break;
    case 1: return $s[0] . $placeholder;
    case 0: return $placeholder . $placeholder;
    }

    $c1 = $s[0];
    $c2 = ctype_space($s[1]) ? $s[2] : $s[1];
    return $c1 . $c2;
}

function download_file_with_info($log, $source_path, $source_url): bool
{
    $download_success = download_file($source_path, $source_url, $status_code);

    if (!$download_success) {
        $message = "download failed";
        if ($status_code)
            $message .= " (HTTP status code $status_code)";

        $log->error($message);
        return false;
    }

    return true;
}

function update_database(string $source_url): bool
{
    $log = get_logger('database');

    $log->info("downloading database from remote...");

    $source_path = temp_filename();
    if (!download_file_with_info($log, $source_path, $source_url)) {
        return false;
    }

    $src_db = new \PDO("sqlite:$source_path");
    $src_db->exec("CREATE INDEX idx_texts_name ON texts(name);");
    $select_stmt = $src_db->prepare(<<<SQL

        SELECT
            id,
            alias,
            name,
            CASE
                WHEN type & 0x40 THEN 'EXTRA' -- Fusion
                WHEN type & 0x2000 THEN 'EXTRA' -- Synchro
                WHEN type & 0x800000 THEN 'EXTRA' -- XYZ
                WHEN type & 0x4000000 THEN 'EXTRA' -- Link
                ELSE 'MAIN'
            END AS type,
            -- one name may only be matched with one card (not multiple).
            LAG(alias, 1, NULL) OVER w1 IS NULL AS match_name
        FROM texts
        INNER JOIN datas USING(id)
        WINDOW w1 AS (
            PARTITION BY name
            ORDER BY
                alias = 0 DESC, -- prefer original cards
                ot < 4 DESC, -- prefer official cards
                id -- prefer smaller IDs
        )

    SQL);
    $select_stmt->execute();
    $rows = $select_stmt->fetchAll(\PDO::FETCH_ASSOC);

    $src_db = null; // close connection
    unlink($source_path); // not needed anymore


    $write_path = temp_filename();
    $dest_db = new \PDO("sqlite:$write_path");
    $dest_db->exec(<<<SQL

        CREATE TABLE card (
            id INTEGER PRIMARY KEY,
            alias INTEGER NOT NULL,
            cluster CHAR(2),
            sanitized_name VARCHAR(128),
            name VARCHAR(128),
            type VARCHAR(8),
            match_name INTEGER
        );

    SQL);
    $dest_db->exec(<<<SQL

        ALTER TABLE card
            ADD CONSTRAINT chk_card_type CHECK(
                type IN ('MAIN', 'EXTRA')
            );

    SQL);
    $dest_db->exec("CREATE INDEX idx_card_cluster ON card(cluster);");
    $dest_db->exec("CREATE INDEX idx_card_name ON card(sanitized_name);");
    $dest_db->exec("CREATE INDEX idx_card_type ON card(type);");
    $dest_db->exec("CREATE INDEX idx_card_match_name ON card(match_name);");

    # TODO: fetch source rows in a chunked manner.

    $cards = [];
    foreach ($rows as $row) {
        $name = $row['name'];
        $sanitized_name = sanitize_name($name);
        $name_cluster = name_cluster($sanitized_name);

        $cards[] = [
            intval($row['id']),
            intval($row['alias']),
            $name_cluster,
            $sanitized_name,
            $name,
            $row['type'],
            intval($row['match_name'])
        ];
    }

    $dest_db->beginTransaction();
    $columns = [ 'id', 'alias', 'cluster', 'sanitized_name', 'name', 'type', 'match_name' ];
    $retval = insert_chunked($dest_db, 'card', $columns, $cards, 256);

    if (!$retval) {
        $log->error('failed to insert rows');
        return false;
    }

    $dest_db->commit();

    $dest_path = Config::get('repository')['path'];
    rename($write_path, $dest_path);
    chmod($dest_path, 0664); // anyone may read

    $log->info("SUCCESS - database update completed");

    return true;
}

function update_image_urls(): bool
{
    $log = get_logger('database');

    $log->info("updating image urls...");

    $lookup_json_url = getenv('CARD_IMAGE_LOOKUP_JSON_URL');
    $url_prefix = getenv('CARD_IMAGE_URL');
    $url_postfix = getenv('CARD_IMAGE_URL_EXT');

    $dest_path = Config::get('image_urls')['lookup_json_path'];

    if (file_exists($dest_path)) {
        unlink($dest_path);
    }

    $has_image_source = false;

    $json_urls = null;

    if ($lookup_json_url !== false) {
        $has_image_source = true;

        $write_path = temp_filename();
        if (!download_file_with_info($log, $write_path, $lookup_json_url)) {
            return false;
        }
        $contents = file_get_contents($write_path);
        $json_urls = json_decode($contents, true);
        unlink($write_path);
    }

    $lookup_table = [];

    if ($url_prefix !== false && $url_postfix !== false) {
        $has_image_source = true;

        $url = rtrim($url_prefix, "/");
        $extension = ltrim($url_postfix, ".");

        $db = Config\create_repository_pdo();
        $cards = $db->query(<<<SQL

            SELECT c1.id, c1.alias, GROUP_CONCAT(c2.id, ',') AS same_name_ids FROM card AS c1
            LEFT JOIN card AS c2 ON c1.name = c2.name AND c1.id <> c2.id
            GROUP BY c1.id
            ORDER BY CAST(c1.id AS TEXT)

        SQL);

        foreach ($cards as $row) {
            $code = $row['id'];
            $alias = $row['alias'];
            $same_name_ids = $row['same_name_ids'];

            $result = ["$url/$code.$extension"];
            $ids = [intval($code)];

            if (!empty($alias) && intval($alias) !== 0) {
                $result[] = "$url/$alias.$extension";
                $ids[] = intval($alias);
            }
            if ($same_name_ids !== null) {
                foreach (explode(',', $same_name_ids) as $id) {
                    $result[] = "$url/$id.$extension";
                    $ids[] = intval($id);
                }
            }
            if ($json_urls != null) {
                foreach ($ids as $id) {
                    if (array_key_exists($id, $json_urls)) {
                        $result[] = $json_urls[$id];
                    }
                }
            }

            $lookup_table["$code"] = array_unique($result);
        }
    }
    else if ($json_urls !== null) {
        foreach ($json_urls as $key => $value) {
            if (!array_key_exists($key, $lookup_table)) {
                $lookup_table["$key"] = [];
            }
            $lookup_table["$key"][] = $value;
        }
    }



    if (!$has_image_source) {
        $log->error('missing card image source in config');
        return false;
    }

    $data = json_encode($lookup_table);
    file_put_contents($dest_path, $data);
    chmod($dest_path, 0664); // anyone may read

    $log->info("SUCCESS - image url update completed");

    return true;
}
