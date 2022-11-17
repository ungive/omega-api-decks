<?php

require('../vendor/autoload.php');

use Utility\FileLock;


$log = get_logger('database');


$database_url = getenv('DATABASE_URL');
if ($database_url === false) {
    $log->error("aborting: database url is not defined");
    exit(1);
}


$update_lock = new FileLock(Config::get('repository')['update_lock_file']);
if ($update_lock->is_locked()) {
    $log->warning("aborting: an update seems to be in progress already");
    exit(1);
}

$update_lock->lock();
Db\update_database($database_url);
$update_lock->unlock();
