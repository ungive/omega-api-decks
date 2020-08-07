<?php

require('../vendor/autoload.php');

use Format\FormatDecodeException;
use Http\Http;
use Image\Image;
use Image\ImageType;

use Render\CellFactory;
use Render\CellOverlap;
use Render\Rectangle;
use Render\Spacing;
use Render\Table;
use Render\TableLayout;
use Render\Vector;


$log = get_logger('deck-list');

error_reporting(E_ALL & ~E_WARNING);


Http::allow_method('GET');

$input = Http::get_query_parameter('list');
$decoder = Config\get_decoder();

try {
    $decks = $decoder->decode($input);
}
catch (FormatDecodeException $e) {
    $message = $e->getMessage();
    Http::fail("could not detect input format: $message", Http::BAD_REQUEST);
}
catch (Exception $e) {
    $message = $e->getMessage();
    Http::fail("an error occured while handling your request: $message");
}

try {
    $decks->validate(true);
}
catch (\Exception $e) {
    Http::fail($e->getMessage());
}

$main  = $decks->main;
$extra = $decks->extra;
$side  = $decks->side;



$y = 60;
$width = 902;
$spacing = new Spacing(10, 7);

$tables = [
    'main' => [
        'root' => new Vector(25, $y),
        'spacing' => $spacing,
        'width' => $width,
        'height' => 526,
        'layout' => [
            'primary' => TableLayout::LEFT_TO_RIGHT,
            'secondary' => TableLayout::TOP_TO_BOTTOM
        ],
        'overlap' => CellOverlap::VERTICAL
    ],
    'extra' => [
        'root' => new Vector(646, $y),
        'spacing' => $spacing,
        'width' => $width,
        'height' => CARD_HEIGHT,
        'layout' => [
            'primary' => TableLayout::LEFT_TO_RIGHT,
            'secondary' => TableLayout::TOP_TO_BOTTOM
        ],
        'overlap' => CellOverlap::HORIZONTAL
    ],
    'side' => [
        'root' => new Vector(822, $y),
        'spacing' => $spacing,
        'width' => $width,
        'height' => CARD_HEIGHT,
        'layout' => [
            'primary' => TableLayout::LEFT_TO_RIGHT,
            'secondary' => TableLayout::TOP_TO_BOTTOM
        ],
        'overlap' => CellOverlap::HORIZONTAL
    ]
];


function create_table(string $name): Table
{
    $card_size    = new Rectangle(CARD_WIDTH, CARD_HEIGHT);
    $cell_factory = new CellFactory(
        $card_size->width(),
        $card_size->height()
    );

    global $tables;
    $c = $tables[$name];

    $table = new Table($c['width'], $c['height'], $cell_factory);

    $table->layout($c['layout']['primary'], $c['layout']['secondary']);
    $table->overlap($c['overlap']);

    $table->root($c['root']->x(), $c['root']->y());
    $table->spacing($c['spacing']->horizontal(), $c['spacing']->vertical());

    return $table;
}



// $table = new Table(902, 526, $cell_factory);

// $table->layout(TableLayout::LEFT_TO_RIGHT, TableLayout::TOP_TO_BOTTOM);
// $table->overlap(CellOverlap::VERTICAL);

// $table->root(25, 60);
// $table->spacing(10, 7);


$card_size = new Rectangle(CARD_WIDTH, CARD_HEIGHT);

$table = create_table('main');


foreach ($main->cards() as $card)
    $table->push($card);


$cache = Config\get_image_cache();

foreach ($main->unique_card_codes() as $code)
    $cache->load($code);

if (CACHE_ORIGINAL)
    $cache->flush();

foreach ($main->unique_card_codes() as $code) {
    $entry = $cache->get($code);
    if ($entry === null)
        Http::fail("an unknown error occured while trying to load a card image");

    $image = $entry->image();
    $image->resize($card_size->width(), $card_size->height(), RESAMPLE_CARDS);
}

if (!CACHE_ORIGINAL)
    $cache->flush();


$deck_image = Image::from_file(BACKGROUND_IMAGE);
if ($deck_image === null)
    Http::fail("failed to open deck background", Http::INTERNAL_SERVER_ERROR);

# TODO: put this somewhere else.
if ($deck_image->type() === ImageType::PNG) {
    imagealphablending($deck_image->handle(), false);
    imagesavealpha($deck_image->handle(), true);
}

foreach ($table->cells() as $cell) {

    $card = $cell->content();
    $image = $cache->get($card->code)->image();
    $position = $cell->position();

    $dimensions = new Rectangle(
        $cell->visible_width(),
        $cell->visible_height()
    );

    $cell_offset = new Vector(
        $cell->x_offset(),
        $cell->y_offset()
    );

    $deck_image->insert(
        $image,
        $position->plus($cell_offset),
        $cell_offset,
        $dimensions,
        $dimensions
    );
}


$image_type = ImageType::from_format(DECK_IMAGE_FORMAT);
$mime_type  = ImageType::mime_type($image_type);
$extension  = ImageType::extension($image_type, false);

$filename = 'deck-list';

header("Content-Type: $mime_type");
header("Content-Disposition: filename=$filename.$extension");

$deck_image->echo($image_type, false);


# TODO: make configurable that you can iterate
#  backwards, so that cards can overlap the other way round

# TODO: try drawing cards in random order

# TODO: print numbers with font 'Eurostile Regular'.
#  https://fonts.adobe.com/fonts/eurostile
