<?php

namespace Format;


class SeparatedParsedCardList
{
    const SEPARATOR_ELEMENT = null;

    private array $cards;

    public function __construct(ParsedCard ...$cards)
    {
        $this->cards = $cards;
    }

    public function add(ParsedCard $card)
    {
        $this->cards[] = $card;
    }

    public function add_separator()
    {
        $this->cards[] = self::SEPARATOR_ELEMENT;
    }


}
