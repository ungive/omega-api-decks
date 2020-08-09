<?php

namespace Format;

use \Game\DeckList;


interface FormatEncoder
{
    function encode(DeckList $deck): string;
}
