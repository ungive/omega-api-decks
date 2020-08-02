<?php

namespace Game\Data;

use \Game\DeckType;


class YgoprodeckRepository extends Repository
{
    public function get_cards_by_code(int ...$codes): DataCardList
    {
        $query = http_build_query([ 'id' => implode(',', $codes) ]);
        $url = $this->base_url . '?' . $query;

        $data = file_get_contents($url);
        if ($data === false)
            throw new \Exception("could not retrieve URL");

        $data = json_decode($data)->data;
        $result = new DataCardList();

        foreach ($data as $card) {
            $deck_type = $this->get_deck_type($card->type);
            $result[$card->id] = new DataCard($card->id, $deck_type);
        }

        return $result;
    }

    public function get_cards_by_name(string ...$codes): DataCardList
    {
        throw new \Exception("not implemented");
    }

    private function get_deck_type(string $card_type): int
    {
        switch (strtolower($card_type)) {
        case "effect monster":
        case "flip effect monster":
        case "flip tuner effect monster":
        case "gemini monster":
        case "normal monster":
        case "normal tuner monster":
        case "pendulum effect monster":
        case "pendulum flip effect monster":
        case "pendulum normal monster":
        case "pendulum tuner effect monster":
        case "ritual effect monster":
        case "ritual monster":
        case "skill card":
        case "spell card":
        case "spirit monster":
        case "toon monster":
        case "trap card":
        case "tuner monster":
        case "union effect monster":
            return DeckType::MAIN;

        case "fusion monster":
        case "link monster":
        case "pendulum effect fusion monster":
        case "synchro monster":
        case "synchro pendulum effect monster":
        case "synchro tuner monster":
        case "xyz monster":
        case "xyz pendulum effect monster":
            return DeckType::EXTRA;
        }

        assert(false, "unrecognized card type '$card_type',"
            . " defaulting to Main Deck");
        return DeckType::MAIN;
    }
}
