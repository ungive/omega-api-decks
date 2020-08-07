<?php

namespace Render;


class Rectangle
{
    protected int $width;
    protected int $height;

    public function __construct(int $width, int $height)
    {
        $this->width  = $width;
        $this->height = $height;
    }

    public function width():  int { return $this->width; }
    public function height(): int { return $this->height; }

    public function plus(Rectangle $other): Rectangle
    {
        return new Rectangle(
            $this->width  + $other->width,
            $this->height + $other->height
        );
    }
}
