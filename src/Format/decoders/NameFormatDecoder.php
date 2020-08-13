<?php

namespace Format;

use Game\Deck;
use Game\DeckList;
use Game\DeckType;
use Game\ExtraDeck;
use Game\MainDeck;
use Game\SideDeck;


class NameFormatDecoder extends NeedsRepository implements FormatDecoder
{
    // needed amount of empty spaces before the side deck
    // in case there are no extra deck cards.
    const SIDE_PRECEDING_EMPTY_LINES = 2;

    const LINE_CARD_REGEX = "/\\s*(\\d+)x?\\s\\s*(.+)\\s*/";

    public function decode(string $input): DeckList
    {
        $deck_list = new DeckList();
        $current_deck = null;

        $cards = [];

        $deck_names = [];
        foreach (array_keys(iterator_to_array($deck_list->decks())) as $name)
            $deck_names[] = strtolower($name);
        $deck_names = implode('|', $deck_names);

        // a card at index n is the first card of the last consecutive
        // block of cards that is preceded by n lines of whitespace.
        // all cards after the greatest number of empty lines will be
        // considered side deck cards (assuming there are no extra deck cards).
        $potential_first_side_cards = [];
        $separator_height = 0;

        $separator_count = 0;

        $entries = explode("\n", trim($input));
        foreach ($entries as $index => $entry) {
            $line = $index + 1;
            $entry = trim($entry);

            if (strlen($entry) === 0) {
                $separator_height++;
                continue;
            }

            $matches = [];
            $result = preg_match(self::LINE_CARD_REGEX, $entry, $matches);

            if (!$result) {
                $result = preg_match("/($deck_names)/", strtolower($entry), $matches);
                if (!$result)
                    throw new FormatDecodeException(
                        "unable to parse line $line of input");

                $deck_name = $matches[1];
                $deck = $deck_list->$deck_name;

                if ($current_deck === null) {
                    // in case there are cards before the first
                    // separator we add all of them to the Main Deck.
                    foreach ($cards as $card)
                        $deck_list->main->add($card);
                    $cards = [];
                }

                $current_deck = $deck;
                continue;
            }

            $card_count = intval($matches[1]);
            $card_name = $matches[2];

            $card = $this->repository->get_card_by_name($card_name);

            if ($current_deck != null) {
                $current_deck->add($card, $card_count);
                continue;
            }

            // before we save the "first side card", we need to make sure
            // that up until this point the main deck has enough cards,
            // otherwise it wouldn't make sense to potentially put the
            // remaining cards into the side deck.
            $main_deck_satisfied = count($cards) >= MainDeck::MIN_SIZE;

            for ($i = 0; $i < $card_count; $i++)
                $cards[] = $card;

            if ($separator_height > 0) {
                if ($main_deck_satisfied)
                    $potential_first_side_cards[$separator_height] = $card;
                $separator_height = 0;
                $separator_count++;
            }
        }

        // separators were used, so we're done here.
        if ($current_deck !== null)
            return $deck_list;

        $extra_deck = $deck_list->extra;
        $side_deck  = $deck_list->side;
        $main_deck  = $deck_list->main;

        // will hold the first card before the first extra
        // deck card, or null if there are no extra deck cards.
        $card_before_extra_deck = null;

        $move_card = function (int $key, array &$src, Deck $dest) {
            if (count($dest) >= $dest::MAX_SIZE)
                return false;

            $dest->add($src[$key]);
            unset($src[$key]);

            return true;
        };

        // the first 15 extra deck cards go into the extra deck.
        foreach ($cards as $key => $card) {

            if ($card->deck_type !== DeckType::EXTRA) {
                if (count($extra_deck) === 0)
                    $card_before_extra_deck = $card;
                continue;
            }

            // move the cards of this entry to the extra deck.
            if ($move_card($key, $cards, $extra_deck))
                continue;

            // if not all cards could be added (because the extra deck
            // is full), then add the remaining cards to the side deck.
            if (!$move_card($key, $cards, $side_deck))
                throw new FormatDecodeException(implode(" ", [
                    "too many Extra Deck cards, no more than",
                    ExtraDeck::MAX_SIZE + SideDeck::MAX_SIZE,
                    "allowed"
                ]));
        }

        $consecutive_spaces = 0; // largest number of consecutive spaces.
        if (count($potential_first_side_cards) > 0)
            $consecutive_spaces = max(array_keys($potential_first_side_cards));

        // allow a single space with only one separating line.
        if ($separator_count > 1)
            if ($consecutive_spaces < self::SIDE_PRECEDING_EMPTY_LINES)
                $consecutive_spaces = 0;

        if (count($extra_deck) === 0 && $consecutive_spaces > 0) {
            // there are no extra deck cards, so in order to separate
            // main deck cards from side deck cards, we split at the last
            // separator entry that is found in our card list.

            $first_side_card = $potential_first_side_cards[$consecutive_spaces];

            $found_first = false;
            foreach ($cards as $key => $card) {
                if (!$found_first && $card !== $first_side_card)
                    continue;

                $found_first = true;
                $move_card($key, $cards, $side_deck);
            }
        }
        else {
            // we're iterating in reverse, so we can't immediately add
            // side deck cards. store them and reverse them again.
            $side_count = count($side_deck);
            $side_keys = [];

            // the cards from the end of the list until the position at which
            // the first extra deck card was encountered belong to the side deck.
            foreach (array_reverse($cards, true) as $key => $card) {
                if ($card === $card_before_extra_deck)
                    break; // done
                if ($side_count < $side_deck::MAX_SIZE) {
                    $side_keys[] = $key;
                    $side_count ++;
                    continue;
                }
                // there are cards that don't fit into the side deck anymore.
                // those will be added to the main deck instead, even though
                // they appeared behind the first extra deck card.
                break;
            }

            foreach (array_reverse($side_keys) as $key)
                if (!$move_card($key, $cards, $side_deck))
                    throw new FormatDecodeException(
                        "this shouldn't happen, please file an issue");
        }

        // the remaining cards go to the main deck.
        foreach ($cards as $key => $card)
            if (!$move_card($key, $cards, $main_deck))
                break;

        // if there are still cards remaining, try to put them into the side deck
        foreach($cards as $key => $card)
            if (!$move_card($key, $cards, $side_deck))
                throw new FormatDecodeException(
                    "too many cards, Main and Side Deck have reached their limit");

        return $deck_list;
    }
}
