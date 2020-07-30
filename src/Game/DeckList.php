<?php

namespace Game;


class DeckList implements \ArrayAccess
{
    const DECK_COUNT = 3;

    private array $decks = [];

    public function __construct()
    {

    }

    public function offsetSet($deck_type, $deck)
    {
        if ($deck_type === null) {
            if ($deck->type === null)
                throw new \InvalidArgumentException("missing deck type");

            return $this->offsetSet($deck->type, $deck);
        }

        $this->decks[$deck_type] = $deck;
    }

    public function offsetExists($deck_type)
    {
        return isset($this->decks[$deck_type]);
    }

    public function offsetUnset($deck_type)
    {
        unset($this->decks[$deck_type]);
    }

    public function offsetGet($deck_type)
    {
        if ($this->offsetExists($deck_type))
            return $this->decks[$deck_type];

        return null;
    }


    // public function get(int $deck_type)
    // {
    //     switch ($deck_type) {
    //     case DeckType::MAIN:  return $this->main;
    //     case DeckType::EXTRA: return $this->extra;
    //     case DeckType::SIDE:  return $this->side;
    //     case DeckType::UNKNOWN: return null;
    //     }

    //     assert(false, "deck type does not exist");
    //     return null;
    // }
}
