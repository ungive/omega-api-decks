<?php

namespace Format;

use \Game\DeckList;


class YdkFormatStrategy implements FormatEncodeStrategy, FormatDecodeStrategy
{
    public function encode(DeckList $list): string
    {
        throw new FormatException("not implemented");
    }

    public function decode(string $encoded): DeckList
    {
        throw new FormatDecodeException("not implemented");
    }
}
