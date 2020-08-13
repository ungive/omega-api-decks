<?php

namespace Game\Repository;

use Game\Card;


abstract class Repository
{
    public NameMatchOptions $options;

    public function __construct(NameMatchOptions $options)
    {
        $this->options = $options;
    }

    public abstract function get_card_by_code(int $code): Card;
    public abstract function get_card_by_name(string $name): Card;
    public abstract function get_name_by_code(int $code): string;
}
