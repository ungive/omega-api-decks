<?php

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/common/format.php');

use Image\Image;
use Image\ImageType;
use Render\Rectangle;
use Render\Vector;


Http::allow_method('GET');
Http::check_token('token', 'REQUEST_TOKEN');

$decks = Base\decode_query_deck();


$cache = Config\create_image_cache();

$cell_dimensions  = Config::get('cell');
$cache_original   = Config::get('cache')['cache_original'];
$resample_resized = Config::get('images')['resample_resized'];

foreach ($decks->unique_card_codes() as $code)
    $cache->load($code);

if ($cache_original)
    $cache->flush();

foreach ($decks->unique_card_codes() as $code) {
    $entry = $cache->get($code);
    if ($entry === null)
        Http::fail("an unexpected error occured whilst loading a card image");

    $image = $entry->image();
    $image->resize(
        $cell_dimensions->width(),
        $cell_dimensions->height(),
        $resample_resized
    );
}

if (!$cache_original)
    $cache->flush();


$deck_image = Image::from_file(Config::get('images')['background']);
if ($deck_image === null)
    Http::fail("failed to open deck background", Http::INTERNAL_SERVER_ERROR);

// preserve alpha when working with PNG images.
if ($deck_image->type() === ImageType::PNG) {
    imagealphablending($deck_image->handle(), false);
    imagesavealpha($deck_image->handle(), true);
}


$tables = [
    Config\create_deck_table('main',  $decks->main),
    Config\create_deck_table('extra', $decks->extra),
    Config\create_deck_table('side',  $decks->side),
];

foreach ($tables as $table)
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


$image_type = Config::get('output')['image_type'];
$filename   = Config::get('output')['content_disposition']['filename'];

$mime_type = ImageType::mime_type($image_type);
$extension = ImageType::extension($image_type, false);

header("Content-Type: $mime_type");
header("Content-Disposition: filename=$filename.$extension");

$quality_raw = Http::get_query_parameter('quality', false, -1);
$quality = intval($quality_raw);

if (!is_numeric($quality_raw)) Http::fail("quality must be numeric");
if ($quality < -1 || $quality > 100)
    Http::fail("quality must be between 0 and 100, inclusive");

switch ($image_type) {
case ImageType::JPEG: break;
case ImageType::PNG:
    $quality = intval(round($quality * 9 / 100));
    break;
default:
    Http::fail("the quality parameter is not applicable for $mime_type");
}

$deck_image->echo($image_type, false, $quality);



# TODO: cache generated images for a certain amount of time.
#  good opportunity to learn Redis e.g.



# TODO: make configurable that you can iterate
#  backwards, so that cards can overlap the other way round

// $cells = (function () use ($tables) {
//     $cells = [];
//     foreach ($tables as $table)
//         foreach ($table->cells() as $cell)
//             $cells[] = $cell;
//
//     foreach(array_reverse($cells) as $cell)
//         yield $cell;
// })();

# TODO: try drawing cards in random order

// $cells = (function () use ($tables) {
//     $cells = [];
//     foreach ($tables as $table)
//         foreach ($table->cells() as $cell)
//             $cells[] = $cell;
//
//     while (count($cells) > 0) {
//         $key = array_rand($cells);
//         get_logger('i')->info($key);
//         $cell = $cells[$key];
//         unset($cells[$key]);
//         yield $cell;
//     }
// })();

# TODO: print numbers with font 'Eurostile Regular'.
#  https://fonts.adobe.com/fonts/eurostile
