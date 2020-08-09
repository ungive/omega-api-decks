<?php

require(__DIR__ . '/../vendor/autoload.php');

use Format\FormatDecodeException;

use Http\Http;
use Image\Image;
use Image\ImageType;
use Render\Rectangle;
use Render\Vector;


$log = get_logger('create');

// disable warnings when errors are displayed because the omega deck code
// can emit a gzip warning that messes up any image that we try to transmit.
if (ini_get("display_errors") === '1')
    error_reporting(E_ALL & ~E_WARNING);


Http::allow_method('GET');

$input = Http::get_query_parameter('list');

try {
    $decoder = Config\create_decoder();
    $decks = $decoder->decode($input);
}
catch (FormatDecodeException $e) {
    $message = $e->getMessage();
    Http::fail("failed to decode: $message", Http::BAD_REQUEST);
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

$deck_image->echo($image_type, false);



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
