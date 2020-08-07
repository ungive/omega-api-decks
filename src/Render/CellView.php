<?php

namespace Render;


class CellView extends Cell
{
    private int $visible_width;
    private int $visible_height;

    private int $x_offset;
    private int $y_offset;

    private Vector $position;

    public function __construct(
        int $width,         int $height,
        int $visible_width, int $visible_height,
        int $x_offset,      int $y_offset,
        Vector $position,
        $content = null
    ) {
        parent::__construct($width, $height, $content);

        $this->visible_width  = $visible_width;
        $this->visible_height = $visible_height;

        $this->x_offset = $x_offset;
        $this->y_offset = $y_offset;

        $this->position = $position;
    }

    public function visible_width():  int { return $this->visible_width; }
    public function visible_height(): int { return $this->visible_height; }

    public function x_offset(): int { return $this->x_offset; }
    public function y_offset(): int { return $this->y_offset; }

    public function position(): Vector { return $this->position; }

    public function x(): int { return $this->position->x(); }
    public function y(): int { return $this->position->y(); }
}
