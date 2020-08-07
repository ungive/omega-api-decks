<?php

namespace Render;


class CellFactory extends Rectangle
{
    public function create($content)
    {
        return new Cell($this->width, $this->height, $content);
    }
}
