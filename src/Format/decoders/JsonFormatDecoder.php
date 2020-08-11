<?php

namespace Format;

use Game\DeckList;

class JsonFormatDecoder implements FormatDecoder
{
    const MAX_DEPTH = 8;

    public function decode(string $encoded): DeckList
    {
        try {
            return DeckList::from_json($encoded);
        }
        catch (\Exception $e) {
            $message = $e->getMessage();
            throw new FormatDecodeException(lcfirst($message));
        }
    }
}
