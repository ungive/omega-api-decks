<?php

namespace Render;


class Vector
{
    protected int $x;
    protected int $y;

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function x(): int { return $this->x; }
    public function y(): int { return $this->y; }

    public function plus(Vector $other): Vector
    {
        return new Vector(
            $this->x + $other->x,
            $this->y + $other->y
        );
    }
}
