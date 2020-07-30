<?php

namespace Format;

use \Game\Card;
use \Game\DeckType;

use \Utility\BitField;


class ParsedCard
{
    public ?int $code;
    public ?string $name;

    /**
    * the locations (DeckType) in which this card could be.
    * most formats do not distinguish this unambiguously.
    */
    public BitField $deck_types;

    public function __construct(?int $code = null, ?string $name = null,
                                 int $deck_types = DeckType::UNKNOWN)
    {
        $this->code = $code;
        $this->name = $name;

        $this->deck_types = new BitField($deck_types);
    }

    public static function with_code(int $code,
                                     int $deck_types = DeckType::UNKNOWN)
        : ParsedCard
    {
        return new ParsedCard($code, null, $deck_types);
    }

    public static function with_name(string $name,
                                     int $deck_types = DeckType::UNKNOWN)
        : ParsedCard
    {
        return new ParsedCard(null, $name, $deck_types);
    }

    public function has_code(): bool { return $this->code !== null; }
    public function has_name(): bool { return $this->name !== null; }

    public function is_valid(): bool
    {
        if (!$this->has_code()) return false;
        if (count($this->deck_types) !== 1) return false;

        return true;
    }

    public function to_card(): Card
    {
        # assert($this->is_valid(), "converting in an invalid state");

        $deck_type = $this->deck_types->get();

        if (($deck_type & Card::VALID_DECKTYPES) === 0)
            $deck_type = DeckType::UNKNOWN;

        return new Card($this->code, $deck_type);
    }
}
