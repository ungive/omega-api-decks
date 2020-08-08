<?php

require('../vendor/autoload.php');


$log = get_logger('database');


$database_url = getenv('DATABASE_URL');
if ($database_url === false) {
    $log->error("aborting: database url is not defined");
    exit(1);
}


# TODO: check the update lock file here as well.


Db\update($database_url);
