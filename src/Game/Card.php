<?php

namespace Game;


class Card
{
    private int $code;

    public function __construct(int $code)
    {
        $this->code = $code;
    }

    public function code(): int { return $this->code; }
}
