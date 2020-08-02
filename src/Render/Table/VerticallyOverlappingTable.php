<?php

namespace Render\Table;


class VerticallyOverlappingTable extends OverlappingTable
{
    public function __construct($width, $height, $cell) {
        parent::__construct($width, $height, $cell);

        $this->column_overlap = 0;
    }

    public function rows() {
        $column_count = $this->columns();
        assert($column_count > 0, 'there are no columns in this table');
        return intval(ceil($this->cell_count / $column_count));
    }
}
