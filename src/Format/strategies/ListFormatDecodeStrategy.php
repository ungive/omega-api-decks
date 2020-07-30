<?php

namespace Format;

use \Yugioh\Deck;
use \Yugioh\DeckList;

#
# -*- LIST -*-
#
# - any non-extra-deck card goes to the main deck
# - the first 15 extra deck cards go to the extra deck
# - after the first extra deck card:
#   any non-extra-deck card ...
#     if main < 40: ... goes to the side deck
#     else:         ... goes to the main deck
#   any extra-deck card ...
#     ... goes to the side deck
#

#
# - extract the first 15 (or less) extra deck cards -> extra deck
# - extract the last 15 (or less) cards until the position at which
#   the first extra deck card was encountered (inclusive) -> side deck
# - the remaining cards go to the -> main deck
#

class ListFormatDecodeStrategy implements FormatDecodeStrategy
{
    public function decode(string $encoded): DeckList
    {
        throw new FormatDecodeException();
    }
}
