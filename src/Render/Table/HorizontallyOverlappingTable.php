<?php

namespace Render\Table;


class HorizontallyOverlappingTable extends OverlappingTable
{
    public function __construct($width, $height, $cell) {
        parent::__construct($width, $height, $cell);

        $this->row_overlap = 0;
    }

    public function columns() {
        $row_count = $this->rows();
        assert($row_count > 0, 'there are no rows in this table');
        return intval(ceil($this->cell_count / $row_count));
    }
}
