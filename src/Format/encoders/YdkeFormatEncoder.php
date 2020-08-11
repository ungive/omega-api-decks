<?php

namespace Format;

use Game\Deck;
use Game\DeckList;


class YdkeFormatEncoder implements FormatEncoder
{
    public function encode(DeckList $list): string
    {
        $encoded = YdkeFormat::PREFIX;

        $parts = [
            $this->encode_deck($list->main),
            $this->encode_deck($list->extra),
            $this->encode_deck($list->side)
        ];

        $encoded .= implode(YdkeFormat::SEPARATOR, $parts);
        $encoded .= YdkeFormat::SUFFIX;

        return $encoded;
    }

    private function encode_deck(Deck $deck): string
    {
        $raw = "";
        foreach ($deck->cards() as $card)
            $raw .= pack('V', $card->code);
        return base64_encode($raw);
    }
}
