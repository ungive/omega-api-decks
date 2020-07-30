<?php

namespace Game;


class Deck implements \Countable
{
    /**
    * sentinel for auto-detecting the type of a deck.
    */
    const DETECT_TYPE = -1;

    private array $entries = [];
    private int $type = DeckType::UNKNOWN;

    private int $count = 0;

    /**
    * creates a new deck from a list of cards
    * @param CardList $cards the cards
    * @param int $type the deck type.
    *   if not passed the type is derived from the first card
    */
    public function __construct(int $type = self::DETECT_TYPE,
                                ?CardList $cards = null)
    {
        if ($cards === null)
            return;

        $this->type = $type;

        if ($type === self::DETECT_TYPE) {
            if (count($cards) === 0)
                throw new Exception("cannot guess deck type without cards");

            $this->type = $cards[0]->deck_type;
        }

        foreach ($cards as $card)
            $this->add($card);
    }

    public function set(Card $card, int $count): DeckEntry
    {
        $this->count += $count;
        return $this->entries[$card->code] = new DeckEntry($card, $count);
    }

    public function add(Card $card, int $count = 1): DeckEntry
    {
        if ($this->type !== DeckType::SIDE)
            if ($card->deck_type !== $this->type)
                throw new DeckTypeMismatchException("incompatible deck types");

        if ($entry = $this->get_or_null($card)) {
            $entry->add_count($count);
            $this->count += $count;
            return $entry;
        }

        return $this->set($card, $count);
    }

    public function remove(Card $card, $count = DeckEntry::MAX_COUNT): void
    {
        if (!$this->has($card))
            return;

        $entry = $this->get($card);

        if (count($entry) <= $count) {
            $this->count -= count($entry);
            $this->unset_entry($card);
            return;
        }

        $this->entries[$card]->subtract_count($count);
        $this->count -= $count;
    }

    public function remove_one(Card $card): void
    {
        $this->remove($card, 1);
    }

    public function has(Card $card): bool
    {
        return $this->get_entry($card) !== null;
    }

    public function get(Card $card): DeckEntry
    {
        return $this->entries[$card->code];
    }

    public function get_or_null(Card $card): ?DeckEntry
    {
        return $this->has($card) ? $this->get($card) : null;
    }

    public function get_type(): int { return $this->type; }

    public function count(): int { return $this->count; }


    private function get_entry(Card $card): ?DeckEntry
    {
        if (isset($this->entries[$card->code]))
            return $this->entries[$card->code];

        return null;
    }

    private function set_entry(Card $card, DeckEntry $entry): DeckEntry
    {
        return $this->entries[$card->code] = $entry;
    }

    private function unset_entry(DeckEntry $entry): void
    {
        unset($this->entries[$entry->card()->code]);
    }
}


class DeckTypeMismatchException extends \Exception {}
