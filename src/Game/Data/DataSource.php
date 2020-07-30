<?php

namespace Game\Data;

use \Game\CardList;


abstract class DataSource
{
    protected string $base_url;

    public function __construct(string $base_url)
    {
        $this->base_url = $base_url;
    }

    /**
    * complements a list of cards by fetching missing or wrong data.
    * @param $cards card list
    * @return the complemented list of cards
    */
    public function complement_cards(CardList $cards): CardList;

    public function fetch_cards_by_codes(array $codes): CardList;
    public function fetch_cards_by_names(array $names): CardList;

    public function fetch_missing_ids(CardList $cards): void;


    public function determine_locations (CardList &$cards): void;
    public function determine_names     (CardList &$cards): void;
}
