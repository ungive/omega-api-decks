<?php

namespace Format;

use \Game\DeckList;


class OmegaFormatEncoder implements FormatEncoder
{
    public function encode(DeckList $list): string
    {
        $raw  = "";
        $raw .= pack('C', count($list->main) + count($list->extra));
        $raw .= pack('C', count($list->side));
        $raw .= pack('V*', ...$list->main->card_codes());
        $raw .= pack('V*', ...$list->extra->card_codes());
        $raw .= pack('V*', ...$list->side->card_codes());

        $deflated = gzdeflate($raw);
        $encoded = base64_encode($deflated);

        return $encoded;
    }
}
