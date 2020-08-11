<?php

namespace Base;

require_once(__DIR__ . '/_base.php');

use Http;
use Http\JsonResponse;

function get_query_pretty(): bool
{
    return Http::get_query_parameter('pretty', false) !== null;
}

function get_query_json_options(): int
{
    return get_query_pretty() ? JSON_PRETTY_PRINT : 0;
}

function create_json_response(?string $meta_format = null): JsonResponse
{
    $response = new JsonResponse();
    $response->options(get_query_json_options());

    if ($meta_format !== null)
        $response->meta('format', $meta_format);

    return $response;
}
