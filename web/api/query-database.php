<?php

require('../../vendor/autoload.php');




$db = new PDO('sqlite:' . DB_FILE);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);


$starttime = microtime(true);
while (microtime(true) - $starttime < 3) {
    $select_stmt = $db->prepare(<<<SQL
        SELECT id, name, type, match_name FROM card
        LIMIT 1;
    SQL);
    $select_stmt->execute();
    $rows = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
}




exit;






$handle = db_lock();
if (!$handle)
    die;

var_dump("acquired lock!");
sleep(1);

db_unlock($handle);




// $path = '../../db/OmegaDB.cdb';
// $db = new PDO('sqlite:' . $path);

// $db->sqliteCreateFunction('levenshtein', 'levenshtein', 1);
