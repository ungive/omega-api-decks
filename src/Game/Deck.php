<?php

namespace Game;
# TODO: rename to Domain


# TODO: make abstract and add abstract static method get_upper_limit() and get_lower_limit()
class Deck implements \Countable
{
    private array $entries = [];
    private int $count = 0;

    public function __construct(?CardList $cards = null)
    {
        if ($cards !== null)
            foreach ($cards as $card)
                $this->add($card);
    }

    public function set(Card $card, int $count): DeckEntry
    {
        if (isset($this->entries[$card->code()]))
            $this->count -= $this->entries[$card->code()]->count;

        $this->count += $count;
        return $this->set_entry($card, new DeckEntry($card, $count));
    }

    public function add(Card $card, int $count = 1): DeckEntry
    {
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

        $entry->subtract_count($count);
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
        return $this->entries[$card->code()];
    }

    public function get_or_null(Card $card): ?DeckEntry
    {
        return $this->has($card) ? $this->get($card) : null;
    }

    public function count(): int { return $this->count; }


    public function cards(): \Generator
    {
        foreach ($this->entries as $entry)
            for ($i = 0; $i < count($entry); $i++)
                yield $entry->card();
    }

    public function card_codes(): \Generator
    {
        foreach ($this->cards() as $card)
            yield $card->code();
    }


# private:

    private function get_entry(Card $card): ?DeckEntry
    {
        if (isset($this->entries[$card->code()]))
            return $this->entries[$card->code()];

        return null;
    }

    private function set_entry(Card $card, DeckEntry $entry): DeckEntry
    {
        return $this->entries[$card->code()] = $entry;
    }

    private function unset_entry(DeckEntry $entry): void
    {
        unset($this->entries[$entry->card()->code]);
    }
}
