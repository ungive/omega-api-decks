<?php


class Card
{
    public int $code;
    public int $deck_type;

    public function __construct(int $code, int $deck_type)
    {
        $this->code = $code;
        $this->deck_type = $deck_type;
    }
}

class Cards extends \IteratorIterator
{
    public function __construct(Card ...$cards)
    {
        parent::__construct(new ArrayIterator($cards));
    }

    public function current() : Card
    {
        return parent::current();
    }
}
