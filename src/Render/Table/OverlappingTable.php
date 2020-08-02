<?php

namespace Render\Table;

use Render\Position;


abstract class OverlappingTable extends Table
{
    protected $cell_count = 0;
    protected $row_overlap = 0;
    protected $column_overlap = 0;

    public function set_cell_count($count) {
        $this->cell_count     = $count;
        $this->row_overlap    = $this->overlap($this->height, $this->cell->height, $this->rows());
        $this->column_overlap = $this->overlap($this->width,  $this->cell->width,  $this->columns());
    }

    public function cell_position($index) {
        $row = intval($index / $this->columns());
        $col = $index % $this->columns();

        # TODO: make this configurable (l->r;t->b <-> t->b;l->r)
        // $col = intval($index / $this->rows());
        // $row = $index % $this->rows();

        return new Position(
            $col * $this->cell->width  - $col * $this->column_overlap,
            $row * $this->cell->height - $row * $this->row_overlap
        );
    }

    protected function overlap($length, $element_length, $element_count) {
        if ($element_count <= 1)
            return 0;

        $visible = ($length - $element_length) / ($element_count - 1);
        $hidden  = max(0, $element_length - $visible);

        return $hidden;
    }
}
