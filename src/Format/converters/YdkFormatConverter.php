<?php

namespace Format;

use Game\Card;
use \Game\DeckList;
use Game\DeckType;


class YdkFormatConverter implements FormatConverter
{
    const COMMENT_REGEX = "/^(?:#|!)\s*(.*)$/";
    const CARD_CODE_REGEX = "/^([0-9]+)$/";

    public function encode(DeckList $list): string
    {
        throw new FormatException("not implemented");
    }

    public function decode(string $encoded): DeckList
    {
        $deck_list = new DeckList();
        $deck = null;

        foreach (explode("\n", trim($encoded)) as $index => $line) {
            $l = $index + 1;
            $line = trim($line);

            if (strlen($line) === 0)
                continue; // ignore empty lines

            if (preg_match(self::COMMENT_REGEX, $line, $matches)) {
                switch ($content = strtolower($matches[1])) {
                case 'main': case 'extra': case 'side':
                    $deck = $deck_list->$content; // current deck
                }
                continue;
            }

            if (preg_match(self::CARD_CODE_REGEX, $line, $matches)) {
                $code = intval($matches[1]);
                if ($deck === null)
                    throw new FormatDecodeException(
                        "unable to associate code $code with any deck on line $l");

                $type = $deck::TYPE;
                if ($type === DeckType::SIDE)
                    $type = DeckType::UNKNOWN;

                $deck->add(new Card($code, $type));
                continue;
            }

            throw new FormatDecodeException(
                "unable to match line $l of input, expected a comment or a card code");
        }

        return $deck_list;
    }
}
