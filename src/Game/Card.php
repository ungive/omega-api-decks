<?php

namespace Game;


class Card
{
    public int $code;
    public int $deck_type;

    public function __construct(int $code, int $deck_type = DeckType::UNKNOWN)
    {
        $this->code = $code;
        $this->deck_type = $deck_type;
    }

    public function code(): int { return $this->code; }
    public function deck_type(): int { return $this->deck_type; }
}
