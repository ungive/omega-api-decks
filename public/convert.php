<?php

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/common/format.php');
require(__DIR__ . '/common/json.php');


Http::allow_method('GET');

$convert_to = Http::get_query_parameter('to', false);
$encoders   = Config::get('formats')['encoders'];

if ($convert_to !== null) {
    $supported_encoders = Config::get('formats')['encoders'];
    if (!isset($supported_encoders[$convert_to]))
        Http::fail("'$convert_to' is not a supported format", Http::BAD_REQUEST);

    $encoders = [ $convert_to => $supported_encoders[$convert_to] ];
}

foreach ($encoders as $name => $class)
    $encoders[$name] = Config\create_encoder_from_class($class);

$decks    = Base\decode_query_deck($input_format);
$response = Base\create_json_response($input_format);

$formats = [];
foreach ($encoders as $name => $encoder)
    $formats[$name] = $encoder->encode($decks);

$response->data('formats', $formats);

Http::send($response);
