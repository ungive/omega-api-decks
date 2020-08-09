<?php

namespace Format;

use \Game\DeckList;


interface FormatDecoder
{
    function decode(string $encoded): DeckList;
}
