<?php

namespace Render;


class Spacing
{
    protected float $horizontal;
    protected float $vertical;

    public function __construct(float $horizontal, float $vertical)
    {
        $this->horizontal = $horizontal;
        $this->vertical   = $vertical;
    }

    public function horizontal(): float { return $this->horizontal; }
    public function vertical():   float { return $this->vertical; }
}
