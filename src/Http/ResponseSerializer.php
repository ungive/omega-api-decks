<?php

namespace Http;

use \Http\Response;


interface ResponseSerializer
{
    function serialize(Response $response): string;
    function content_type(): string;
}
