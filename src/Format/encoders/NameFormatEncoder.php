<?php

namespace Format;

use Game\Deck;
use Game\DeckList;


class NameFormatEncoder extends NeedsRepository implements FormatEncoder
{
    const EOL = "\n";

    public function encode(DeckList $list): string
    {
        $main  = $this->encode_deck($list->main);
        $extra = $this->encode_deck($list->extra);
        $side  = $this->encode_deck($list->side);

        // extra line break before the side deck.
        $side = self::EOL . $side;

        $separator = self::EOL . self::EOL;
        return implode($separator, array_filter([ &$main, &$extra, &$side ]));
    }

    public function encode_deck(Deck $deck): string
    {
        $encoded = "";

        foreach ($deck->entries() as $entry) {
            $card = $entry->card();
            $name = $this->repository->get_name_by_code($card->code());

            $encoded .= count($entry) . ' ';
            $encoded .= $name . self::EOL;
        }

        return trim($encoded);
    }
}
