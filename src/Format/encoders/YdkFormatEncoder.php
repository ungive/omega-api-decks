<?php

namespace Format;

use Game\DeckList;


class YdkFormatEncoder implements FormatEncoder
{
    // Prefer Windows line endings over Unix
    // because most users are on that platform.
    const EOL = "\n";

    public function encode(DeckList $list): string
    {
        $encoded  = "";

        $encoded .= '#main' . self::EOL;
        $encoded .= implode(self::EOL, iterator_to_array($list->main->card_codes())) . self::EOL;

        $encoded .= '#extra' . self::EOL;
        $encoded .= implode(self::EOL, iterator_to_array($list->extra->card_codes())) . self::EOL;

        $encoded .= '!side' . self::EOL;
        $encoded .= implode(self::EOL, iterator_to_array($list->side->card_codes()));

        return $encoded;
    }
}
