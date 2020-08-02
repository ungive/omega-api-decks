<?php

namespace Http;


abstract class Response
{
    public int $code;
    public string $type;

    public function __construct(int $code, string $type)
    {
        $this->code = $code;
        $this->type = $type;
    }
}
