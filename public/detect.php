<?php

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/common/format.php');
require(__DIR__ . '/common/json.php');


Http::allow_method('GET');

$decks    = Base\decode_query_deck($input_format);
$response = Base\create_json_response($input_format);

Http::send($response);
