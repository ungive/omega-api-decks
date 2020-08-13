<?php

namespace Format;

use Game\DeckList;


class JsonFormatEncoder implements FormatEncoder
{
    public function encode(DeckList $list): string
    {
        return $list->to_json();
    }
}
