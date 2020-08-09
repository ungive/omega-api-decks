<?php

namespace Format;

use Game\DeckList;


class FormatDecodeTester implements FormatDecoder
{
    private $decoders = [];

    function register(FormatDecoder $decoder): void
    {
        $this->decoders[] = $decoder;
    }

    function decode(string $input): DeckList
    {
        if (count($this->decoders) === 0)
            throw new FormatDecodeException(
                "cannot decode without any decoders");

        foreach ($this->decoders as $decoder)
            try {
                $list = $decoder->decode($input);
                $exception = null;
                break;
            }
            catch (FormatDecodeException $e) {
                $exception = $e;
            }

        if ($exception !== null)
            throw new FormatDecodeException(
                "could not determine format from input");

        return $list;
    }
}
