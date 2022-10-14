<?php

// continue in background
ignore_user_abort(true);
set_time_limit(0);


require('../../vendor/autoload.php');

use Utility\FileLock;


$log = get_logger('webhook');
$log->info("received request: " . basename(__FILE__));


$token_param = 'token';
if (!isset($_GET[$token_param])) {
    $log->alert("aborting: security token not set");
    Http::close(Http::NOT_FOUND);
}

$expected_token = getenv('WEBHOOK_UPDATE_TOKEN');
if ($expected_token !== false && !hash_equals($_GET[$token_param], $expected_token)) {
    $log->critical("aborting: invalid token: " . $_GET[$token_param]);
    Http::close(Http::NOT_FOUND);
}

$database_url = getenv('DATABASE_URL');
if ($database_url === false) {
    $log->error("aborting: database url is not defined");
    Http::close(Http::INTERNAL_SERVER_ERROR);
}


// close the connection
http_response_code(Http::OK);
header("Connection: close");
// https://github.com/docker-library/php/issues/1113
header("Content-Encoding: none");
header("Content-Length: " . 0);
ob_end_flush();
flush();


$update_lock = new FileLock(Config::get('repository')['update_lock_file']);
if ($update_lock->is_locked()) {
    $log->warning("aborting: an update seems to be in progress already");
    exit(1);
}

$update_lock->lock();
Db\update($database_url);
$update_lock->unlock();
