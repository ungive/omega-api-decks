<?php

require('../../vendor/autoload.php');





$tmp = DB_FILE . '.copy';
copy(DB_FILE, $tmp);
rename($tmp, DB_FILE);









exit;




$handle = db_lock();
if (!$handle)
    die;

sleep(20);

db_unlock($handle);

