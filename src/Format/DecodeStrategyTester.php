<?php

namespace Format;

use Game\DeckList;


class DecodeStrategyTester implements FormatDecodeStrategy
{
    private $strategies = [];

    function register(FormatDecodeStrategy $strategy): void
    {
        $this->strategies[] = $strategy;
    }

    function decode(string $input): DeckList
    {
        if (count($this->strategies) === 0)
            throw new FormatDecodeException(
                "cannot decode without any strategies");

        foreach ($this->strategies as $strategy)
            try {
                $list = $strategy->decode($input);
                $exception = null;
                break;
            }
            catch (FormatDecodeException $e) {
                $exception = $e;
            }

        if ($exception !== null)
            throw $exception;

        return $list;
    }
}
