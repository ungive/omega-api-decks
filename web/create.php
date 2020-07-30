<?php

require('../vendor/autoload.php');



# accept POST



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



class Cell  extends Dimensions {}
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



abstract class OverlapStrategy
{
    public function row_offset() {
        return $this->overlap($this->height, $this->cell->height, $this->rows());
    }

    public function column_offset();

    protected function overlap($length, $element_length, $element_count) {
        if ($element_count <= 1)
            return 0;

        $visible = ($length - $element_length) / ($element_count - 1);
        $hidden  = max(0, $element_length - $visible);

        return $hidden;
    }
}

class HorizontalOverlapStrategy extends OverlapStrategy
{

}

class VerticalOverlapStrategy extends OverlapStrategy
{

}


abstract class PlacementStrategy
{

}

class LeftToRightPlacementStrategy extends PlacementStrategy
{

}

class TopToBottomPlacementStrategy extends PlacementStrategy
{

}


class OverlappingTable extends Table
{
    public $overlap_strategy;
    public $placement_strategy;

    public function __construct($overlap_strategy, $placement_strategy) {
        $this->overlap_strategy = $overlap_strategy;
        $this->placement_strategy = $placement_strategy;
    }


}





// $main_table = new OverlappingTable($width, $height, $cell, [
//     'overlap_strategy' => new HorizontalOverlapStrategy(),
//     'placement_strategy' => new LeftToRightPlacementStrategy()
// ]);

// for ($i = 0; $i < 40; $i++)
//     $main_table->add(0);

// foreach ($main_table->entries() as $entry) {
//     $position = $entry->position;
//     $value = $entry->value;
// }


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






const MIN_ENTRIES = 40;
const MAX_ENTRIES = 60;

const HORIZONTAL_PADDING = 10;
const VERTICAL_PADDING = 7;



$entries = array_fill(0, 48, 0);
$card = new Dimensions(81, 118);



$root = new Position(24, 60);
$cell = new Cell(HORIZONTAL_PADDING, VERTICAL_PADDING);
$cell->add($card);

$table = new VerticallyOverlappingTable($cell->get_width() * 10, 525, $cell);

# $table = new HorizontallyOverlappingTable(910, $cell->get_height() * 1, $cell);

$table->set_cell_count(count($entries));




# newImagick;scale;composite;setImageFormat;getImageBlob:  2.2 - 2.7 sec

$time_pre = microtime(true);




$deck_image = new Imagick("./static/background.bmp");

foreach ($entries as $index => $entry) {

    $card_image = new Imagick("./cache/{$entry}.jpg");
    $card_image->scaleImage(0, $card->get_height());
    $card_image->setImageFormat('bmp');

    $position = $table->cell_position($index);
    $position->add($root);

    $deck_image->compositeImage(
        $card_image,
        Imagick::COMPOSITE_DEFAULT,
        $position->get_x(), $position->get_y()
    );
}


$deck_image->setImageFormat('png');
$result = $deck_image->getImageBlob();


$time_post = microtime(true);
$exec_time = $time_post - $time_pre;
# var_dump($exec_time);
# exit;



header("Content-type: image/png");
header("Content-Disposition: filename=deck-image.png");
echo $result;
exit;




define('IMAGE_CDN', getenv('IMAGE_CDN'));

function load_url($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 2500);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

    $data = curl_exec($ch);

    if (in_array(curl_errno($ch), [
        CURLE_OPERATION_TIMEDOUT,
        CURLE_OPERATION_TIMEOUTED
    ])) $data = file_get_contents('./static/unknown.jpg');

    curl_close($ch);

    return $data;
}

function load_image($id) {
    $url = str_replace('{ID}', $id, IMAGE_CDN);
    return load_url($url);
}

header("Content-type: image/jpeg");
echo load_image(10000000);
