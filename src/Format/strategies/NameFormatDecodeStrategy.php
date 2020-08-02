<?php

namespace Format;

use Game\Data\Repository;
use Game\DeckType;
use Game\MainDeck;

#
# -*- LIST -*-
#
# - any non-extra-deck card goes to the main deck
# - the first 15 extra deck cards go to the extra deck
# - after the first extra deck card:
#   any non-extra-deck card ...
#     if main < 40: ... goes to the side deck
#     else:         ... goes to the main deck
#   any extra-deck card ...
#     ... goes to the side deck
#

#
# - extract the first 15 (or less) extra deck cards -> extra deck
# - extract the last 15 (or less) cards until the position at which
#   the first extra deck card was encountered (inclusive) -> side deck
# - the remaining cards go to the -> main deck
#

#
# in case there are no extra deck cards (acting as separator),
# the cards after the last line break belong to the side deck.
#


class NameFormatDecodeStrategy implements FormatDecodeStrategy
{
    public Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function decode(string $encoded): ParsedCardList
    {
        $groups = [];
        $current = new ParsedCardList();

        $lines = explode("\n", $encoded);
        foreach ($lines as $index => $line) {
            $line = trim($line);

            if (strlen($line) === 0) { // empty line
                if (count($current) > 0) { // previous line was also empty
                    $groups[] = $current;
                    $current = new ParsedCardList();
                }
                continue;
            }

            $matches = [];
            $result = preg_match("/(\\d+)x?\\s\\s*(.*)/", $line, $matches);

            if ($result === 0 || $result === false)
                throw new FormatDecodeException("unable to match line " . ($index + 1));

            $card_count = $matches[1];
            $card_name = $matches[2];

            if (!is_numeric($card_count))
                throw new FormatDecodeException("expected a numeric value");
            if (strlen($card_name) === 0)
                throw new FormatDecodeException("cannot have an empty name");

            $card_count = intval($card_count);

            for ($i = 0; $i < $card_count; $i++)
                $current[] = ParsedCard::with_name($card_name);
        }

        if (count($current) > 0)
            $groups[] = $current;


        $main_count = 0;
        $i = 0; $j = 0;

        // the first 40 cards are definitely in the main deck.
        for (; $i < count($groups); $i++, $j = 0) {
            $cards = $groups[$i];
            for (; $j < count($cards); $j++) {
                if ($main_count === MainDeck::MIN_SIZE)
                    break 2; // break all loops

                $card = $cards[$j];
                $card->deck_types->add(DeckType::MAIN);
                $main_count += 1;
            }
        }

        // remove all groups we've completed.
        array_splice($groups, 0, $i);
        $i = 0; // we removed all preceding elements.

        // remove all remaining cards we've completed.
        $cards = $groups[$i]->array();
        array_splice($cards, 0, $j);
        $groups[$i]->exchangeArray($cards);
        $j = 0;




        $cccount = 0;
        foreach ($groups as $group) {
            foreach ($group as $card) {
                $decks = "";

                if ($card->deck_types->has(DeckType::MAIN)) $decks .= "Main, ";
                if ($card->deck_types->has(DeckType::EXTRA)) $decks .= "Extra, ";
                if ($card->deck_types->has(DeckType::SIDE)) $decks .= "Side, ";

                $cccount += 1;
                var_dump("[" . $cccount . "] " . $card->name . " in " . $decks);
            }
        }

        return $current;
    }


}


// 1 Dark Spirit of Malice
// 1 Dark Spirit of Banishment
// 1 Archfiend Empress
// 1 Curse Necrofear
// 1 Grinder Golem
// 1 Abominable Unchained Soul
// 1 Unchained Soul of Disaster
// 3 Unchained Twins – Sarama
// 3 Unchained Twins – Aruha
// 3 Graff, Malebranche of the Burning Abyss
// 1 Cir, Malebranche of the Burning Abyss
// 1 Scarm, Malebranche of the Burning Abyss
// 1 Barbar, Malebranche of the Burning Abyss
// 2 Tour Guide From the Underworld
// 1 Fiendish Rhino Warrior

// 1 Spirit Message “I”
// 1 Spirit Message “L”
// 1 Spirit Message “N”
// 1 Spirit Message “A”
// 3 Dark Spirit’s Mastery
// 2 Dark Sanctuary
// 3 Abomination’s Prison
// 1 Card Destruction

// 1 Destiny Board
// 3 Abominable Chamber of the Unchained
// 1 Call of the Archfiend

// 2 Unchained Soul of Rage
// 1 Unchained Abomination
// 2 Unchained Soul of Anguish
// 1 Cherubini, Ebon Angel of the Burning Abyss
// 2 Dante, Traveler of the Burning Abyss
// 1 Dante, Pilgrim of the Burning Abyss
// 1 Security Dragon
// 2 Linkuriboh
// 1 Akashic Magician
// 1 Mekk-Knight Crusadia Avramax
// 1 Knightmare Phoenix





// Dark Spirit of Malice
// Dark Spirit of Banishment
// Archfiend Empress
// Curse Necrofear
// Grinder Golem
// Abominable Unchained Soul
// Unchained Soul of Disaster
// Unchained Twins - Sarama
// Unchained Twins - Aruha
// Graff, Malebranche of the Burning Abyss
// Cir, Malebranche of the Burning Abyss
// Scarm, Malebranche of the Burning Abyss
// Barbar, Malebranche of the Burning Abyss
// Tour Guide From the Underworld
// Fiendish Rhino Warrior

// Spirit Message "I"
// Spirit Message "L"
// Spirit Message "N"
// Spirit Message "A"
// Dark Spirit's Mastery
// Dark Sanctuary
// Abomination's Prison
// Card Destruction

// Destiny Board
// Abominable Chamber of the Unchained
// Call of the Archfiend

// Unchained Soul of Rage
// Unchained Abomination
// Unchained Soul of Anguish
// Cherubini, Ebon Angel of the Burning Abyss
// Dante, Traveler of the Burning Abyss
// Dante, Pilgrim of the Burning Abyss
// Security Dragon
// Linkuriboh
// Akashic Magician
// Mekk-Knight Crusadia Avramax
// Knightmare Phoenix
