<?php

namespace Format;

use Game\DeckList;


class FormatDecodeTester implements FormatDecoder
{
    private $decoders = [];

    function register(string $name, FormatDecoder $decoder): void
    {
        $this->decoders[$name] = $decoder;
    }

    # TODO: put effort in something better than this...
    private ?string $last_format_name = null;

    function decode(string $input): DeckList
    {
        if (count($this->decoders) === 0)
            throw new FormatDecodeException(
                "cannot decode without any decoders");

        foreach ($this->decoders as $name => $decoder)
            try {
                $list = $decoder->decode($input);
                $this->last_format_name = $name;
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

    function last_format_name(): ?string { return $this->last_format_name; }
}
