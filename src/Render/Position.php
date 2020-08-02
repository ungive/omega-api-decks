<?php

namespace Render;


class Position
{
    protected $x;
    protected $y;

    public function __construct($x, $y) {
        $this->x = intval($x);
        $this->y = intval($y);
    }

    public function add($other) {
        $this->x += $other->x;
        $this->y += $other->y;
    }

    public function get_x() { return $this->x; }
    public function get_y() { return $this->y; }
}
