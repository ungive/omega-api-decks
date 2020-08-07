<?php

namespace Render;


class Table extends Rectangle implements \Countable
{
    private int $primary_layout   = TableLayout::LEFT_TO_RIGHT;
    private int $secondary_layout = TableLayout::TOP_TO_BOTTOM;

    private int $overlap = CellOverlap::NONE;

    private Vector $root;

    private CellFactory $cell_factory;
    private array $cells = [];

    private int $rows;
    private int $columns;

    private Spacing $min_spacing;
    private Spacing $max_spacing;

    public function __construct(int $width, int $height,
                                CellFactory $cell_factory)
    {
        parent::__construct($width, $height);

        if ($cell_factory->width() === 0)
            throw new \Exception("a table's cell cannot have a width of 0");

        if ($cell_factory->height() === 0)
            throw new \Exception("a table's cell cannot have a height of 0");

        $this->root = new Vector(0, 0);

        $this->cell_factory = $cell_factory;

        $this->min_spacing = new Spacing(0, 0);
        $this->max_spacing = new Spacing(0, 0);

        $this->rows =    $this->calculate_rows();
        $this->columns = $this->calculate_columns();
    }

    public function count(): int { return count($this->cells); }

    public function primary_layout():   int { return $this->primary_layout; }
    public function secondary_layout(): int { return $this->secondary_layout; }

    public function cell_factory(): CellFactory { return $this->cell_factory; }

    public function root(int $x, int $y): void
    {
        $this->root = new Vector($x, $y);
    }

    public function min_spacing(float $horizontal, ?float $vertical = null): void
    {
        if ($vertical === null)
            $vertical = $horizontal;

        $horizontal = max(0, $horizontal);
        $vertical   = max(0, $vertical);

        $this->min_spacing = new Spacing($horizontal, $vertical);
        $this->max_spacing = new Spacing(
            max($this->max_spacing->horizontal(), $horizontal),
            max($this->max_spacing->vertical(),   $vertical)
        );

        $this->rows    = $this->calculate_rows();
        $this->columns = $this->calculate_columns();
    }

    public function max_spacing(float $horizontal, ?float $vertical = null): void
    {
        if ($vertical === null)
            $vertical = $horizontal;

        $horizontal = max(0, $horizontal);
        $vertical   = max(0, $vertical);

        $this->max_spacing = new Spacing($horizontal, $vertical);
        $this->min_spacing = new Spacing(
            min($this->min_spacing->horizontal(), $horizontal),
            min($this->min_spacing->vertical(),   $vertical)
        );

        $this->rows    = $this->calculate_rows();
        $this->columns = $this->calculate_columns();
    }

    public function spacing(float $horizontal, ?float $vertical = null): void
    {
        if ($vertical === null)
            $vertical = $horizontal;

        $horizontal = max(0, $horizontal);
        $vertical   = max(0, $vertical);

        $spacing = new Spacing($horizontal, $vertical);
        $this->min_spacing = $spacing;
        $this->max_spacing = $spacing;

        $this->rows    = $this->calculate_rows();
        $this->columns = $this->calculate_columns();
    }

    public function dynamic_spacing(bool $dynamic_spacing): void
    {
        $this->max_spacing = $dynamic_spacing
            ? new Spacing(PHP_INT_MAX, PHP_INT_MAX)
            : $this->min_spacing;
    }

    public function layout(int $primary, int $secondary): void
    {
        if ($primary === TableLayout::opposite($secondary))
            throw new \Exception("cannot have two opposing layouts");

        $this->primary_layout   = $primary;
        $this->secondary_layout = $secondary;
    }

    public function overlap(?int $overlap = null): int
    {
        if ($overlap !== null)
            $this->overlap = $overlap;

        return $this->overlap;
    }

    public function rows(): int
    {
        if ($this->overlap === CellOverlap::VERTICAL) {
            $rows = intval(ceil(count($this->cells) / $this->columns()));
            return max($rows, $this->rows);
        }

        return $this->rows;
    }

    public function columns(): int
    {
        if ($this->overlap === CellOverlap::HORIZONTAL) {
            $cols = intval(ceil(count($this->cells) / $this->rows()));
            return max($cols, $this->columns);
        }

        return $this->columns;
    }

    public function push($item): Cell
    {
        if ($this->cell_factory === null)
            throw new \Exception("cannot push an item without a cell factory");

        if (count($this->cells) > $this->capacity())
            throw new \Exception("capacity of table reached");

        $cell = $this->cell_factory->create($item);
        $this->cells[] = $cell;

        return $cell;
    }

    public function cells(): \Generator
    {
        $count = count($this->cells); # in for loop expression?
        for ($index = 0; $index < $count; $index ++)
            yield $this->create_cell_view($index);
    }

    public function capacity(): int
    {
        // the capacity is unlimited when cells can overlap.
        # TODO: actually, if it were unlimited then at some point cells
        #  would overlap so much, that some wouldn't be visible anymore.
        if ($this->overlap !== CellOverlap::NONE)
            return PHP_INT_MAX;

        return $this->rows() * $this->columns();
    }

    protected function calculate_rows(): int
    {
        $cell_height  = $this->cell_factory->height;
        $cell_height += $this->min_spacing->vertical();

        $total_height = max(0, $this->height - $this->cell_factory->height);
        $rows         = intval(floor($total_height / $cell_height)) + 1;

        return $rows;
    }

    protected function calculate_columns(): int
    {
        $cell_width  = $this->cell_factory->width;
        $cell_width += $this->min_spacing->horizontal();

        $total_width = max(0, $this->width - $this->cell_factory->width);
        $columns     = intval(floor($total_width / $cell_width)) + 1;

        return $columns;
    }

    protected function create_cell_view(int $index): CellView
    {
        $primary   = $this->primary_layout;
        $secondary = $this->secondary_layout;

        $rows = $this->rows();
        $cols = $this->columns();

        $row = $col = 0;

        if (TableLayout::is_horizontal($primary)) {
            $row = intval($index / $cols);
            $col = $index % $cols;

            if ($primary === TableLayout::RIGHT_TO_LEFT)
                $col = $cols - $col - 1;

            if ($secondary === TableLayout::BOTTOM_TO_TOP)
                $row = $rows - $row - 1;
        }

        if (TableLayout::is_vertical($primary)) {
            $col = intval($index / $rows);
            $row = $index % $rows;

            if ($primary === TableLayout::BOTTOM_TO_TOP)
                $row = $rows - $row - 1;

            if ($secondary === TableLayout::RIGHT_TO_LEFT)
                $col = $cols - $col - 1;
        }

        $cell_width  = $this->cell_factory->width();
        $cell_height = $this->cell_factory->height();

        $cell_count  = count($this->cells);

        $row_overlap = $col_overlap = 0;

        if ($this->overlap === CellOverlap::HORIZONTAL)
            $col_overlap = $this->calculate_overlap(
                $this->width,  $cell_width,  $cols);

        if ($this->overlap === CellOverlap::VERTICAL)
            $row_overlap = $this->calculate_overlap(
                $this->height, $cell_height, $rows);

        assert( # TODO: handle this differently
            $col_overlap < $cell_width &&
            $row_overlap < $cell_height,
            "overlapping an entire cell"
        );

        $spacing = $this->calculate_spacing();

        $col_spacing = intval($col * $spacing->horizontal());
        $row_spacing = intval($row * $spacing->vertical());

        $is_row_cut_off = $is_col_cut_off = false;
        $x_offset       = $y_offset       = 0;

        if (TableLayout::is_horizontal($primary)) {
            $is_row_cut_off = $index + $cols < $cell_count;
            $is_col_cut_off = $index + 1 < $cell_count && $index % $cols !== $cols - 1;
        }

        if (TableLayout::is_vertical($primary)) {
            $is_col_cut_off = $index + $rows < $cell_count;
            $is_row_cut_off = $index + 1 < $cell_count && $index % $rows !== $rows - 1;
        }

        if ($is_row_cut_off)
            if ($primary   === TableLayout::BOTTOM_TO_TOP ||
                $secondary === TableLayout::BOTTOM_TO_TOP)
                    $y_offset = $row_overlap;

        if ($is_col_cut_off)
            if ($primary   === TableLayout::RIGHT_TO_LEFT ||
                $secondary === TableLayout::RIGHT_TO_LEFT)
                    $x_offset = $col_overlap;

        $visible_width  = $cell_width  - $is_col_cut_off * $col_overlap;
        $visible_height = $cell_height - $is_row_cut_off * $row_overlap;

        $x = $this->root->x();
        $y = $this->root->y();

        return new CellView(
            $cell_width,    $cell_height,
            $visible_width, $visible_height,
            $x_offset,      $y_offset,
            new Vector(
                $x + $col * $cell_width  - $col * $col_overlap + $col_spacing,
                $y + $row * $cell_height - $row * $row_overlap + $row_spacing
            ),
            $this->cells[$index]->content()
        );
    }

    protected function calculate_spacing(): Spacing
    {
        $rows = $this->rows();
        $cols = $this->columns();

        $total_width  = $this->cell_factory->width()  * $cols;
        $total_height = $this->cell_factory->height() * $rows;

        $unused_width  = max(0, $this->width  - $total_width);
        $unused_height = max(0, $this->height - $total_height);

        $horizontal = $cols > 1 ? $unused_width  / ($cols - 1) : 0;
        $vertical   = $rows > 1 ? $unused_height / ($rows - 1) : 0;

        $horizontal = min($horizontal, $this->max_spacing->horizontal());
        $vertical   = min($vertical,   $this->max_spacing->vertical());

        if ($this->overlap !== CellOverlap::HORIZONTAL)
            $horizontal = max($horizontal, $this->min_spacing->horizontal());

        if ($this->overlap !== CellOverlap::VERTICAL)
            $vertical = max($vertical, $this->min_spacing->vertical());

        return new Spacing($horizontal, $vertical);
    }

    protected function calculate_overlap
        (int $total_length, int $part_length, int $count): float
    {
        if ($count <= 1)
            return 0;

        $visible = ($total_length - $part_length) / ($count - 1);
        $hidden  = max(0.0, $part_length - $visible);

        return $hidden;
    }
}
