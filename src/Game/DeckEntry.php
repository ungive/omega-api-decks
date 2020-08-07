<?php

namespace Game;


class DeckEntry implements \Countable
{
    const MAX_COUNT = 3;

    public Card $card;

    private int $count = 0;

    public function __construct(Card $card, int $count = 1)
    {
        $this->card = $card;
        $this->set_count($count);
    }

    public function card(): Card { return $this->card; }
    public function count(): int { return $this->count; }

    public function set_count(int $count): void
    {
        assert($count >= 1, "an entry cannot have less than one card");

        if ($count > self::MAX_COUNT)
            throw new CopyLimitExceededException(
                "maximum number of copies exceeded");

        $this->count = $count;
    }

    public function add_count(int $count): void
    {
        $this->set_count($this->count + $count);
    }

    public function subtract_count(int $count): void
    {
        $this->add_count((-1) * $count);
    }

    public function increment(): void { $this->add_count(1); }
    public function decrement(): void { $this->subtract_count(1); }
}


class CopyLimitExceededException extends \Exception {}
