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

        throw new \Exception("not implemented");



        $parts = array_map($this->encode_deck, [
            $list->filter_deck_type(CardType::MAIN_DECK),
            $list->filter_deck_type(CardType::EXTRA_DECK),
            $list->filter_deck_type(CardType::SIDE_DECK)
        ]);

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

        $deck_list = new ParsedCardList();
        $deck_list->append_all($this->decode_deck($parts[0], DeckType::MAIN));
        $deck_list->append_all($this->decode_deck($parts[1], DeckType::EXTRA));
        $deck_list->append_all($this->decode_deck($parts[2], DeckType::SIDE));

        return $deck_list;
    }

    private function encode_deck(Deck $deck): string
    {
        return base64_encode(pack('V*', ...$deck->cards));
    }

    private function decode_deck(string $encoded, int $deck_type): ParsedCardList
    {
        if (!($raw = base64_decode($encoded, true)))
            throw new FormatDecodeException("malformed base64");

        return ParsedCardList::from_codes(unpack("V*", $raw), $deck_type);
    }
}
