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

function update(string $source_url): bool
{
    $log = get_logger('database');

    $log->info("downloading database from remote...");

    $source_path = temp_filename();
    $download_success = download_file($source_path, $source_url, $status_code);

    if (!$download_success) {
        $message = "download failed ";
        if ($status_code)
            $message .= " (HTTP status code $status_code)";

        $log->error($message);
        return false;
    }

    $log->info("download successful. converting...");


    $src_db = new \PDO("sqlite:$source_path");
    $src_db->exec("CREATE INDEX idx_texts_name ON texts(name);");
    $select_stmt = $src_db->prepare(<<<SQL

        SELECT
            id,
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
            $name_cluster,
            $sanitized_name,
            $name,
            $row['type'],
            intval($row['match_name'])
        ];
    }

    $dest_db->beginTransaction();
    $columns = [ 'id', 'cluster', 'sanitized_name', 'name', 'type', 'match_name' ];
    $retval = insert_chunked($dest_db, 'card', $columns, $cards, 256);

    if (!$retval) {
        $log->error('failed to insert rows');
        return false;
    }

    $dest_db->commit();

    $dest_path = Config::get('repository')['path'];
    rename($write_path, $dest_path);
    chmod($dest_path, 0664); // anyone may read

    $log->info("SUCCESS - update completed");

    return true;
}
