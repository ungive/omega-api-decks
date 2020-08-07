<?php

namespace Image;


class ImageKey
{
    const NAME_HASH_SEPARATOR = '.';
    const NO_VALUE_PLACEHOLDER = '_';

    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function value() { return $this->value; }
    public function string() { return strval($this->value); }

    public function sanitized(&$removed_count = null): string
    {
        $original = $this->string();

        $key = $original;
        $key = str_replace(self::NAME_HASH_SEPARATOR, '', $key, $c1);
        $key = preg_replace("/[^aA-zZ0-9\-_]/", '', $key, -1, $c2);
        $key = $key === '' ? self::NO_VALUE_PLACEHOLDER : $key;

        $removed_count = $c1 + $c2 - ($key === self::NO_VALUE_PLACEHOLDER);

        return $key;
    }

    public function hash($length = 8): string
    {
        return bin2hex(substr(md5($this->value, true), 0, $length));
    }

    public function filename(): string
    {
        $name = $this->sanitized($missing_chars);

        if ($missing_chars > 0) {
            $name .= self::NAME_HASH_SEPARATOR;
            $name .= $this->hash();
        }

        return $name;
    }
}
