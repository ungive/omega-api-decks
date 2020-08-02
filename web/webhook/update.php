<?php

// run in background
ignore_user_abort(true);
set_time_limit(0);


require('../../vendor/autoload.php');

use function Utility\download_file;
use function Utility\temp_filename;
use Http\Http;


$log = get_logger('database');
$log->info("received webhook request: " . basename(__FILE__));


$token_param = 'token';
if (!isset($_GET[$token_param])) {
    $log->alert("aborting: security token not set");
    Http::exit(Http::NOT_FOUND);
}

if ($_GET[$token_param] !== getenv('WEBHOOK_UPDATE_TOKEN')) {
    $log->critical("aborting: invalid security token: " . $_GET[$token_param]);
    Http::exit(Http::NOT_FOUND);
}


// close the connection
http_response_code(Http::OK);
header("Connection: close");
header("Content-Length: " . 0);
ob_end_flush();
flush();


$update_lock = new FileLock(UPDATE_LOCK_FILE);
if ($update_lock->is_locked()) {
    $log->warning("aborting: an update seems to be already in progress");
    exit(1);
}

$update_lock->lock();
register_shutdown_function(function () use ($update_lock) {
    $update_lock->unlock();
});


$log->info("starting database update process...");
$log->info("downloading database from remote...");


$source_path = temp_filename();
$download_success = download_file($source_path, getenv('DATABASE_URL'), $status_code); // DATABASE_URL

if (!$download_success) {
    $message = "failed to download from remote";
    if ($status_code)
        $message .= " (HTTP status code $status_code)";

    $log->error($message);
    die(1);
}

$log->info("successfully downloaded database. converting...");


$src_db = new PDO('sqlite:' . $source_path);
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
        END as type,
        alias = 0 AS match_name -- when filtering by name, use this card
    FROM texts
    INNER JOIN datas USING(id)
SQL);
$select_stmt->execute();
$rows = $select_stmt->fetchAll(PDO::FETCH_ASSOC);

$src_db = null; // close connection
unlink($source_path); // not needed anymore


$write_path = temp_filename();
$dest_db = new PDO('sqlite:' . $write_path);
$dest_db->exec(<<<SQL
    CREATE TABLE card (
        id INTEGER PRIMARY KEY,
        name TEXT,
        type TEXT,
        match_name INTEGER
    );
SQL);
$dest_db->exec(<<<SQL
    ALTER TABLE card
        ADD CONSTRAINT chk_card_type CHECK(
            type IN ('MAIN', 'EXTRA')
        );
SQL);
$dest_db->exec("CREATE INDEX idx_card_name ON card(name);");
$dest_db->exec("CREATE INDEX idx_card_type ON card(type);");
$dest_db->exec("CREATE INDEX idx_card_match_name ON card(match_name);");

$cards = [];
foreach ($rows as $row)
    $cards[] = [
        intval($row['id']),
        sanitize_name($row['name']),
        $row['type'],
        intval($row['match_name'])
    ];

$dest_db->beginTransaction();
$columns = [ 'id', 'name', 'type', 'match_name' ];
$res = insert_chunked($dest_db, 'card', $columns, $cards, 256);
$dest_db->commit();

$dest_db = null; // close connection


rename($write_path, DB_FILE);
chmod(DB_FILE, 0664); // anyone may read

$log->info("SUCCESS - database was updated successfully");
