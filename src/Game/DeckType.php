<?php

namespace Game;


abstract class DeckType
{
    const UNKNOWN = 0x0;

    const MAIN  = 0x1;
    const EXTRA = 0x2;
    const SIDE  = 0x4;
}
