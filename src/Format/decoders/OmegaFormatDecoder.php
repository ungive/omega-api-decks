<?php

namespace Format;

use \Game\Card;
use \Game\MainDeck;
use \Game\ExtraDeck;
use \Game\SideDeck;
use \Game\DeckType;
use \Game\DeckList;


class OmegaFormatDecoder extends NeedsRepository implements FormatDecoder
{
    public function decode(string $encoded): DeckList
    {
        $encoded = trim($encoded);

        $deflated = base64_decode($encoded);
        if ($deflated === false)
            throw new FormatDecodeException("could not decode base64");

        $raw = gzinflate($deflated);
        if ($raw === false)
            throw new FormatDecodeException("could not inflate compressed data");

        # the first byte contains the size of the main and extra deck.
        $main_and_extra_size = $this->unpack('C', $raw);

        if ($main_and_extra_size < MainDeck::MIN_SIZE + ExtraDeck::MIN_SIZE)
            throw new FormatDecodeException("Main or Extra Deck is too small");
        if ($main_and_extra_size > MainDeck::MAX_SIZE + ExtraDeck::MAX_SIZE)
            throw new FormatDecodeException("Main or Extra Deck is too large");

        # the second byte represents the size of the side deck.
        $side_count = $this->unpack('C', $raw);

        if ($side_count < SideDeck::MIN_SIZE)
            throw new FormatDecodeException("Side Deck is too small");
        if ($side_count > SideDeck::MAX_SIZE)
            throw new FormatDecodeException("Side Deck is too large");

        # the first 40 cards always belong to the main deck.
        # the last 15 cards (or all if no other remain) belong to
        #   either the main or extra deck (not specified in the format).
        # the remaining cards belong to the main deck.

        $main_count = max(MainDeck::MIN_SIZE, $main_and_extra_size - ExtraDeck::MAX_SIZE);
        $main_or_extra_count = $main_and_extra_size - $main_count;

        $deck_list = new DeckList();

        for ($i = 0; $i < $main_count; $i++)
            $deck_list->main->add(new Card($this->unpack_code($raw), DeckType::MAIN));

        for ($i = 0; $i < $main_or_extra_count; $i++) {
            $code = $this->unpack_code($raw);
            $card = $this->repository->get_card_by_code($code);
            $deck_list->get($card->deck_type)->add($card);
        }

        for ($i = 0; $i < $side_count; $i++)
            $deck_list->side->add(new Card($this->unpack_code($raw)));

        return $deck_list;
    }

    private function unpack_code(&$data)
    {
        return $this->unpack('V', $data);
    }

    private function unpack(string $format, string &$data)
    {
        if (!($unpacked = unpack($format, $data)))
            throw new FormatDecodeException("unexpected end of input");

        $count = 1;
        switch ($format[0]) {
        case 'C': $count = 1; break;
        case 'V': $count = 4; break;
        default:
            assert(false, "unhandled format string"); # FIXME
        }

        $data = substr($data, $count);
        return $unpacked[1];
    }
}
