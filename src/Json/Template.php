<?php

namespace Json;


class Template implements \Countable
{
    public array $template;

    public function __construct(array $template = [])
    {
        $this->template = $template;
    }

    public function has_template(): bool
    {
        return count($this->template) > 0;
    }

    public function is_valid(): bool
    {
        return $this->has_template();
    }

    public function count(): int
    {
        return count($this->template);
    }
}
