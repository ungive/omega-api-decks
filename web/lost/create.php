<?php

require('../vendor/autoload.php');

use Render\Position;
use Render\Dimensions;
use Render\Table\Cell;
use Render\Table\Table;
use Render\Table\VerticallyOverlappingTable;
use Render\Table\HorizontallyOverlappingTable;




const HORIZONTAL_PADDING = 10;
const VERTICAL_PADDING = 7;



$entries = array_fill(0, 11, 0);


$card = new Dimensions(81, 118);
$padding = new Cell(10, 7);

$root = new Position(24, 60);
$cell = clone $card;
$cell->add($padding);

$table = new VerticallyOverlappingTable($cell->get_width() * 10, 525, $cell);
# $table = new HorizontallyOverlappingTable(910, $cell->get_height() * 1, $cell);

$table->set_cell_count(count($entries));




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


header("Content-type: image/png");
header("Content-Disposition: filename=deck.png");
echo $result;
exit;
