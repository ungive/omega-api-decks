<?php

namespace Format;

use \Game\CardList;
use \Game\Deck;
use \Game\DeckList;
use \Game\DeckType;

use function \Utility\starts_with;
use function \Utility\ends_with;


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

    public function decode(string $encoded): ParsedCardList
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

        $list = new ParsedCardList();
        $list->append_all($this->decode_deck($parts[0], DeckType::MAIN));
        $list->append_all($this->decode_deck($parts[1], DeckType::EXTRA));
        $list->append_all($this->decode_deck($parts[2], DeckType::SIDE));

        return $list;
    }


    private function encode_deck(Deck $deck): string
    {
        $raw = "";

        foreach ($deck->cards() as $card)
            $raw .= pack('V', $card->code());

        return base64_encode($raw);
    }

    private function decode_deck(string $encoded, int $deck_type): ParsedCardList
    {
        $raw = base64_decode($encoded, true);

        if (!$raw)
            throw new FormatDecodeException("malformed base64");

        return ParsedCardList::from_codes(unpack("V*", $raw), $deck_type);
    }
}
