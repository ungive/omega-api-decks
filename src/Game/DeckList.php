<?php

namespace Game;

use Json\JsonDeserializeException;


class DeckList extends \Json\Serializable implements \Countable
{
    const DECK_COUNT = 3;

    public MainDeck  $main;
    public ExtraDeck $extra;
    public SideDeck  $side;

    public function __construct()
    {
        $this->main  = new MainDeck();
        $this->extra = new ExtraDeck();
        $this->side  = new SideDeck();
    }

    public function get(int $deck_type): Deck
    {
        switch ($deck_type) {
        case DeckType::MAIN:  return $this->main;
        case DeckType::EXTRA: return $this->extra;
        case DeckType::SIDE:  return $this->side;
        case DeckType::UNKNOWN: return null;
        }

        throw new \InvalidArgumentException("deck type does not exist");
    }

    public function decks(): \Generator
    {
        return $this->reflect_decks();
    }

    public function cards(): \Generator
    {
        foreach ($this->decks() as $deck)
            foreach ($deck->cards() as $card)
                yield $card;
    }

    public function unique_cards(): \Generator
    {
        $encountered = [];

        foreach ($this->cards() as $card) {
            $code = $card->code();
            if (isset($encountered[$code]))
                continue;
            $encountered[$code] = true;
            yield $card;
        }
    }

    public function card_codes(): \Generator
    {
        foreach ($this->cards() as $card)
            yield $card->code();
    }

    public function unique_card_codes(): \Generator
    {
        foreach ($this->unique_cards() as $card)
            yield $card->code();
    }

    public function count()
    {
        $sum = 0;
        foreach ($this->decks() as $deck)
            $sum += count($deck);
        return $sum;
    }

    public function validate(bool $allow_too_little = false): void
    {
        foreach ($this->decks() as $deck)
            $deck->validate($allow_too_little);
    }

    public function json_serialize()
    {
        $decks = [];

        foreach ($this->reflect_decks() as $name => $deck)
            $decks[$name] = iterator_to_array($deck->card_codes());

        return $decks;
    }

    public static function json_deserialize($decks): self
    {
        $list  = new DeckList();

        foreach ($list->reflect_decks() as $name => $deck) {
            if (!isset($decks[$name]))
                throw new JsonDeserializeException("deck '$name' does not exist");

            foreach ($decks[$name] as $code)
                $deck->add(new Card($code));
        }

        return $list;
    }

    private function reflect_decks(): \Generator
    {
        $reflection = new \ReflectionClass(self::class);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            if (($type = $property->getType()) === null)
                continue;

            $name  = $property->getName();
            $class = $type->getName();
            if ($class === Deck::class || is_subclass_of($class, Deck::class))
                yield $name => $this->$name;
        }
    }
}
