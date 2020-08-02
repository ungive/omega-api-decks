<?php

namespace Game\Data;


abstract class Repository
{
    protected string $base_url;

    public function __construct(string $base_url)
    {
        $this->base_url = $base_url;
    }

    public abstract function get_cards_by_code(int ...$codes): DataCardList;
    public abstract function get_cards_by_name(string ...$names): DataCardList;
}
