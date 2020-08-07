<?php

namespace Game;


class DeckList
{
    const DECK_COUNT = 3;

    public MainDeck  $main;
    public ExtraDeck $extra;
    public SideDeck  $side;

    public function __construct()
    {
        $this->main  = new MainDeck();
        $this->extra = new ExtraDeck();
        $this->side  = new SideDeck();
    }

    public function get(int $deck_type): Deck
    {
        switch ($deck_type) {
        case DeckType::MAIN:  return $this->main;
        case DeckType::EXTRA: return $this->extra;
        case DeckType::SIDE:  return $this->side;

        case DeckType::UNKNOWN: return null;
        }

        assert(false, "deck type does not exist");
        return null;
    }

    public function decks(): \Generator
    {
        yield $this->main;
        yield $this->extra;
        yield $this->side;
    }

    public function validate(bool $allow_too_little = false): void
    {
        foreach ($this->decks() as $deck)
            $deck->validate($allow_too_little);
    }
}
