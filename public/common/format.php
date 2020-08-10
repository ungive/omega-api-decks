<?php

namespace Base;

require_once(__DIR__ . '/_base.php');

use Config;
use Format\FormatDecodeException;
use Format\FormatDecoder;
use Game\DeckList;
use Http\Http;


const QUERY_ALL_FORMATS = 'list';


function get_supported_formats(): array
{
    $decoders = Config::get('formats')['decoders'];
    $formats  = array_keys($decoders);

    assert(!in_array(QUERY_ALL_FORMATS, $formats),
        "query parameter name for all formats is a format itself");

    return $formats;
}

function get_query_format(): string
{
    if (isset($_GET[QUERY_ALL_FORMATS]))
        return QUERY_ALL_FORMATS;

    $formats = get_supported_formats();

    foreach ($formats as $format)
        if (isset($_GET[$format]))
            return $format;

    $valid_formats = array_merge([ QUERY_ALL_FORMATS ], $formats);
    $valid_formats = implode(', ', $valid_formats);

    Http::fail(
        "missing format in query, allowed formats are: $valid_formats",
        Http::BAD_REQUEST
    );
}

function get_query_encoded_deck(string &$format = null): string
{
    $format = get_query_format();
    return $_GET[$format];
}

function create_format_decoder(string $format): FormatDecoder
{
    return $format === QUERY_ALL_FORMATS
        ? Config\create_decode_tester()
        : Config\create_decoder($format);
}

function decode_query_deck(): DeckList
{
    $input = get_query_encoded_deck($format);

    try {
        $decoder = create_format_decoder($format);
        $decks   = $decoder->decode($input);
    }
    catch (FormatDecodeException $e) {
        $message = $e->getMessage();
        Http::fail("failed to decode deck list: $message", Http::BAD_REQUEST);
    }
    catch (\Exception $e) {
        $message = $e->getMessage();
        Http::fail("an error occured while handling your request: $message");
    }

    try {
        $decks->validate(true);
    }
    catch (\Exception $e) {
        Http::fail($e->getMessage());
    }

    return $decks;
}
