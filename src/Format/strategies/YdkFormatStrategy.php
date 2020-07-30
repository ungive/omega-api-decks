<?php

namespace Format;

use \Yugioh\Deck;
use \Yugioh\DeckList;


class YdkFormatStrategy implements FormatEncodeStrategy, FormatDecodeStrategy
{
    public function encode(DeckList $list): string
    {
        throw new FormatException();
    }

    public function decode(string $encoded): DeckList
    {
        throw new FormatDecodeException();
    }
}
