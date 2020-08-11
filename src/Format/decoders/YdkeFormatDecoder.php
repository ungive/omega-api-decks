<?php

namespace Format;

use \Game\Card;
use \Game\DeckList;
use \Game\DeckType;

use function \Utility\starts_with;
use function \Utility\ends_with;


# TODO: handle errors better and encapsulate un/pack a little more.

class YdkeFormatDecoder implements FormatDecoder
{
    public function decode(string $encoded): DeckList
    {
        if (!starts_with($encoded, YdkeFormat::PREFIX))
            throw new FormatDecodeException("missing prefix: " . YdkeFormat::PREFIX);

        if (!ends_with($encoded, YdkeFormat::SUFFIX))
            throw new FormatDecodeException("missing suffix: " . YdkeFormat::SUFFIX);

        $payload = substr($encoded, strlen(YdkeFormat::PREFIX));
        $payload = substr($payload, 0, -strlen(YdkeFormat::SUFFIX));
        $parts = explode(YdkeFormat::SEPARATOR, $payload);

        if (count($parts) != DeckList::DECK_COUNT)
            throw new FormatDecodeException("invalid number of decks");

        $deck_list = new DeckList();

        foreach ($this->decode_codes($parts[0]) as $code)
            $deck_list->main->add(new Card($code, DeckType::MAIN));

        foreach ($this->decode_codes($parts[1]) as $code)
            $deck_list->extra->add(new Card($code, DeckType::EXTRA));

        foreach ($this->decode_codes($parts[2]) as $code)
            $deck_list->side->add(new Card($code));

        return $deck_list;
    }

    private function decode_codes(string $encoded): array
    {
        if (!($raw = base64_decode($encoded, true)))
            throw new FormatDecodeException("malformed base64");
        return unpack("V*", $raw);
    }
}
