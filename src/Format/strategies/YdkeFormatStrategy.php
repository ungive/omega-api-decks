<?php

namespace Format;

use \Game\Card;
use \Game\Deck;
use \Game\DeckList;
use \Game\DeckType;

use function \Utility\starts_with;
use function \Utility\ends_with;


# TODO: handle errors better and encapsulate un/pack a little more.

class YdkeFormatStrategy implements FormatEncodeStrategy, FormatDecodeStrategy
{
    private const PREFIX = "ydke://";
    private const SEPARATOR = "!";
    private const SUFFIX = "!";

    public function encode(DeckList $list): string
    {
        $encoded = self::PREFIX;

        $parts = [
            $this->encode_deck($list->main),
            $this->encode_deck($list->extra),
            $this->encode_deck($list->side)
        ];

        $encoded .= implode(self::SEPARATOR, $parts);
        $encoded .= self::SUFFIX;

        return $encoded;
    }

    public function decode(string $encoded): DeckList
    {
        if (!starts_with($encoded, self::PREFIX))
            throw new FormatDecodeException("missing prefix: " . self::PREFIX);

        if (!ends_with($encoded, self::SUFFIX))
            throw new FormatDecodeException("missing suffix: " . self::SUFFIX);

        $payload = substr($encoded, strlen(self::PREFIX));
        $payload = substr($payload, 0, -strlen(self::SUFFIX));
        $parts = explode("!", $payload);

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


    private function encode_deck(Deck $deck): string
    {
        $raw = "";
        foreach ($deck->cards() as $card)
            $raw .= pack('V', $card->code);
        return base64_encode($raw);
    }

    private function decode_codes(string $encoded): array
    {
        if (!($raw = base64_decode($encoded, true)))
            throw new FormatDecodeException("malformed base64");
        return unpack("V*", $raw);
    }
}
