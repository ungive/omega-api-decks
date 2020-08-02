<?php

namespace Http\Serializer;

use \Http\Response;


interface ResponseSerializer
{
    function serialize(Response $response): string;
    function get_content_type(): string;
}
