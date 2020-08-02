<?php

namespace Http;


class ErrorResponse extends Response
{
    public string $message;

    public function __construct(int $code, string $message)
    {
        parent::__construct($code, 'error');

        $this->message = $message;
    }
}
