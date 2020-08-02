<?php

namespace Format;

use \Utility\TypedListObject;


class ParsedCardList extends TypedListObject
{
    protected function allowed($value): bool
    {
        return $value instanceof ParsedCard;
    }

    public static function from_codes(array $codes, int $deck_types): ParsedCardList
    {
        $cards = [];
        foreach ($codes as $code)
            $cards[] = ParsedCard::with_code($code, $deck_types);

        return new ParsedCardList($cards);
    }

    public function card_codes(): \Generator
    {
        foreach ($this as $card)
            yield $card->code;
    }
}
