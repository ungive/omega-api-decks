<?php

namespace Format;


class FormatException extends \Exception {}

class FormatDecodeException extends FormatException
{
    public function __construct(?string $message = null, array $errors = [],
                                int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
