<?php

namespace Json;


class Property extends Template
{
    public string $name;

    public function __construct(string $name, ?array $template = [])
    {
        parent::__construct($template);

        $this->name = $name;
    }

    public function is_valid(): bool
    {
        return true;
    }
}
