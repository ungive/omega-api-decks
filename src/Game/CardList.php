<?php

namespace Game;

use \Utility\TypedListObject;


class CardList extends TypedListObject
{
    protected function allowed($value): bool { return $value instanceof Card; }

    public function card_codes(): array { return $this->column('code'); }
    public function card_names(): array { return $this->column('name'); }

    // public static function from_codes(array $codes, int $deck_type): CardList
    // {
    //     # TODO
    //     # return CardList::create(Card::with_code, $codes, $deck_type);

    //     return new CardList(array_map(
    //         function ($code) use ($deck_type) {
    //             return Card::with_code($code, $deck_type);
    //         }, $codes
    //     ));
    // }

    // public static function from_names(array $names, int $deck_type): CardList
    // {
    //     return new CardList(array_map(
    //         function ($name) use ($deck_type) {
    //             return Card::with_name($name, $deck_type);
    //         }, $names
    //     ));
    // }

    // # TODO: callable?
    // public static function create(callable $card_factory, iterable $items, ...$args) : CardList
    // {
    //     return new CardList(array_map(
    //         function ($item) use ($card_factory, $items, $args) {
    //             return $card_factory($item, ...$args);
    //         }, $items
    //     ));
    // }
}
