<?php

namespace Render;


class Dimensions
{
    protected $width;
    protected $height;

    public function __construct($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }

    public function add($other) {
        $this->width += $other->width;
        $this->height += $other->height;
    }

    public function get_width()  { return $this->width;  }
    public function get_height() { return $this->height; }
}
