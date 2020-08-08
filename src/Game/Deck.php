<?php

namespace Game;


class Deck implements \Countable
{
    const TYPE = DeckType::UNKNOWN;

    const MIN_SIZE = 0;
    const MAX_SIZE = PHP_INT_MAX;

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
        if (isset($this->entries[$card->code])) {
            $this->subtract_count($this->entries[$card->code]->count);
            return $this->entries[$card->code];
        }

        return $this->add_entry(new DeckEntry($card, $count));
    }

    public function add(Card $card, int $count = 1): DeckEntry
    {
        if ($entry = $this->get_or_null($card)) {
            $entry->add_count($count);
            $this->add_count($count);
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
            $this->remove_entry($entry);
            return;
        }

        $entry->subtract_count($count);
        $this->subtract_count($count);
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

    public function count(): int { return $this->count; }


    public function entries(): \Generator
    {
        foreach ($this->entries as $entry)
            yield $entry;
    }

    public function cards(): \Generator
    {
        foreach ($this->entries as $entry)
            for ($i = 0; $i < count($entry); $i++)
                yield $entry->card;
    }

    public function unique_cards(): \Generator
    {
        foreach ($this->entries as $entry)
            yield $entry->card;
    }

    public function card_codes(): \Generator
    {
        foreach ($this->cards() as $card)
            yield $card->code;
    }

    public function unique_card_codes(): \Generator
    {
        foreach ($this->unique_cards() as $card)
            yield $card->code;
    }

    public function has_entry(DeckEntry $entry): bool
    {
        if (isset($this->entries[$entry->card->code]))
            return $this->entries[$entry->card->code] === $entry;
        return false;
    }

    public function get_entry(Card $card): ?DeckEntry
    {
        if (isset($this->entries[$card->code]))
            return $this->entries[$card->code];

        return null;
    }

    public function add_entry(DeckEntry $entry): DeckEntry
    {
        if ($this->has_entry($entry))
            return $entry;

        $this->add_count(count($entry));
        return $this->entries[$entry->card->code] = $entry;
    }

    public function remove_entry(DeckEntry $entry): void
    {
        if (!$this->has_entry($entry))
            return;

        $this->subtract_count(count($entry));
        unset($this->entries[$entry->card->code]);
    }

    public function validate(bool $allow_too_little = false): void
    {
        if (!$allow_too_little && $this->count < $this::MIN_SIZE)
            throw new DeckLimitException(
                "the " . $this->get_deck_name() . " cannot have less than " .
                $this::MIN_SIZE . " cards");

        if ($this->count > $this::MAX_SIZE)
            throw new DeckLimitException(
                "the " . $this->get_deck_name() . " cannot have more than " .
                $this::MAX_SIZE . " cards");
    }

    public function get_deck_name(): string
    {
        $class = get_called_class();
        $name = substr($class, strrpos($class, '\\') + 1);
        return trim(preg_replace("/([A-Z])/", ' $1', $name));
    }


    private function add_count(int $diff): void
    {
        $this->count += $diff;
    }

    private function subtract_count(int $diff): void
    {
        $this->add_count((-1) * $diff);
    }
}

class DeckLimitException extends \Exception {}
