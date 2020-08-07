<?php

namespace Format;

use \Game\DeckList;


interface FormatStrategy {}

interface FormatEncodeStrategy extends FormatStrategy
{
    function encode(DeckList $deck): string;
}

interface FormatDecodeStrategy extends FormatStrategy
{
    function decode(string $encoded): DeckList;
}
