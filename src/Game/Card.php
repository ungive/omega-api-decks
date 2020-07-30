<?php

namespace Game;


class Card
{
    const VALID_DECKTYPES = DeckType::UNKNOWN | DeckType::MAIN | DeckType::EXTRA;

    public int $code;
    public int $deck_type;

    public function __construct(int $code, int $deck_type = DeckType::UNKNOWN)
    {
        $this->code = $code;

        assert(($deck_type & ~self::VALID_DECKTYPES) === 0,
            "a card can only belong to either the Main or Extra Deck");

        assert(($deck_type & ($deck_type - 1)) === 0,
            "a card cannot belong to multiple decks");

        $this->deck_type = $deck_type;
    }
}
