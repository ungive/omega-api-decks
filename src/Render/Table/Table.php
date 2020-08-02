<?php

namespace Render\Table;

use Render\Dimensions;


class Table extends Dimensions
{
    protected $cell;

    public function __construct($width, $height, $cell) {
        parent::__construct($width, $height);

        $this->cell = $cell;
    }

    public function rows() {
        assert($this->cell->height > 0, 'cells have no height');
        return intval(ceil($this->height / $this->cell->height));
    }

    public function columns() {
        assert($this->cell->width > 0, 'cells have no width');
        return intval(ceil($this->width / $this->cell->width));
    }

    public function get_cell() { return $this->cell; }
}
