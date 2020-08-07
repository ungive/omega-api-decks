<?php

namespace Render;


class CellOverlap
{
    const NONE = 0;

    const HORIZONTAL = 1;
    const VERTICAL   = 2;

    private int $horizontal;
    private int $vertical;

    public function __construct(int $horizontal, int $vertical)
    {
        $this->horizontal = $horizontal;
        $this->vertical   = $vertical;
    }

    public function horizontal(): int { return $this->horizontal; }
    public function vertical():   int { return $this->vertical; }
}
