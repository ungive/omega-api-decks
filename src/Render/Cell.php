<?php

namespace Render;


class Cell extends Rectangle
{
    private $content;

    public function __construct(int $width, int $height, $content = null)
    {
        parent::__construct($width, $height);

        $this->content = $content;
    }

    public function content() { return $this->content; }
}
