<?php

namespace Format;

use \Yugioh\Deck;
use \Yugioh\DeckList;


# -*- Omega Code -*-
#
# so basically you make a byte array,
# first byte is the main deck count (main+extra deck)
# then 2nd byte is the side deck count
# then 4 bytes for each id (again main and extra deck)
# then the side deck ids again 4 bytes each
# then you need to compress that byte array
# and then encode in base64
# and done

class OmegaFormatStrategy implements FormatEncodeStrategy, FormatDecodeStrategy
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
