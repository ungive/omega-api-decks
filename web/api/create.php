<?php

require('../../vendor/autoload.php');

use Format\FormatDecodeException;
use Format\FormatDecodeStrategy;
use Format\OmegaFormatStrategy;
use Format\NameFormatDecodeStrategy;
use Format\YdkeFormatStrategy;
use Format\ParsedCardList;
use Game\Data\Repository;
use Game\Data\YgoprodeckRepository;
use Game\DeckList;
use Http\Http;

use Render\Position;
use Render\Dimensions;
use Render\Table\VerticallyOverlappingTable;
use Render\Table\HorizontallyOverlappingTable;


function test_decode_strategies(string $input,
                                FormatDecodeStrategy ...$strategies)
    : ParsedCardList
{
    if (count($strategies) === 0)
        throw new Exception("no format strategies available");

    foreach ($strategies as $strategy)
        try {
            $parsed = $strategy->decode($input);
            $exception = null;
            break;
        }
        catch (FormatDecodeException $e) {
            $exception = $e;
        }

    if ($exception !== null)
        throw new FormatDecodeException(
            "could not detect input format: " . $exception->getMessage());

    return $parsed;
}

function decode_input(string $input): ParsedCardList
{
    try {
        // the ordering here matters. most restrictive format should
        // come first, such that we get an error as early as possible.
        $cards = test_decode_strategies($input,
            # new YdkeFormatStrategy(),
            # new YdkFormatStrategy(),
            # new OmegaFormatStrategy(),
            new NameFormatDecodeStrategy()
        );
    }
    catch (FormatDecodeException $e) {
        Http::set_json_error_response(Http::BAD_REQUEST, $e->getMessage());
    }
    catch (Exception $e) {
        Http::set_json_error_response(Http::INTERNAL_SERVER_ERROR,
            "something went wrong while handling your request: " . $e->getMessage());
    }

    return $cards;
}

function create_deck_list(ParsedCardList $cards,
                         ?ParsedCardList &$out_invalid = null): DeckList
{
    $deck_list = new DeckList();

    foreach ($cards as $card) {
        if (!$card->is_valid()) {
            if ($out_invalid !== null)
                $out_invalid[] = $card;
            continue;
        }

        $deck_type = $card->deck_types->get();
        $deck_list->get($deck_type)->add($card->to_card());
    }

    return $deck_list;
}

function fix_deck_list(DeckList &$deck_list,
                       ParsedCardList $invalid_cards,
                       Repository $repository): void
{
    # TODO: filter cards that do not have a name and
    #   call get_cards_by_name() on those.

    # TODO: if a card name here is not found, then
    #   throw an error, since we are then unable to get an image for it.

    try {
        $data = $repository->get_cards_by_code(...$invalid_cards->card_codes());
    }
    catch (Exception $e) {
        Http::set_json_error_response(Http::INTERNAL_SERVER_ERROR,
            "could not retrieve card information");
    }

    foreach ($invalid_cards as $card) {
        if (!isset($data[$card->code]))
            Http::set_json_error_response(Http::INTERNAL_SERVER_ERROR,
                "could not unambiguously assign deck type");

        $info = $data[$card->code];
        $card->deck_types->set($info->deck_type);
        $deck_list->get($info->deck_type)->add($card->to_card());
    }
}

function load_url(string $url, int $timeout = 0): string
{
    try {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        $data = curl_exec($ch);

        if (curl_errno($ch) !== 0)
            throw new Exception(curl_error($ch));
    }
    finally {
        curl_close($ch);
    }

    return $data;
}

function get_image_url(int $id)
{
    $file = $id . '.' . getenv('IMAGE_EXT');
    return getenv('IMAGE_URL') . '/' . $file;
}

function load_card_image(int $id, int $timeout = 0): string
{
    $url = get_image_url($id);

    try {
        $image = load_url($url, $timeout);
    }
    catch (Exception $e) {
        $image = file_get_contents('../static/unknown.jpg');
    }

    return $image;
}



$arrayobj = new ArrayObject([ 1, 2, 3, 4 ]);

$arr = (array)$arrayobj;
array_splice($arr, 0, 2);
$arrayobj->exchangeArray($arr);

var_dump($arrayobj);
exit;



Http::allow_method('GET');

// decode the input with an appropriate algorithm.
$input = Http::get_query_parameter('list');
$cards = decode_input($input);

exit;

// create a deck list from the parsed cards and extract all invalid cards.
$invalid_cards = new ParsedCardList();
$deck_list = create_deck_list($cards, $invalid_cards);

// invalid cards need to be fixed by pulling data from our repository.
if (count($invalid_cards) > 0) {
    $repository = new YgoprodeckRepository(getenv('YGOPRODECK_URL'));
    fix_deck_list($deck_list, $invalid_cards, $repository);
}

var_dump($deck_list);







exit;


# header("Content-type: image/png");
# header("Content-Disposition: filename=deck.png");

$url = get_image_url(40640057);
$imagick = new Imagick($url);
$imagick->setImageFormat('png');

echo $imagick->getImageBlob();
exit;




$card_dimensions = new Dimensions(81, 118);
$card_padding = new Dimensions(10, 7);

$root = new Position(24, 60);
$cell = clone $card_dimensions;
$cell->add($card_padding);

$main_table = new VerticallyOverlappingTable($cell->get_width() * 10, 525, $cell);

$main_table->set_cell_count(count($entries));




$deck_image = new Imagick("./static/background.bmp");

foreach ($entries as $index => $entry) {

    $card_image = new Imagick("./cache/{$entry}.jpg");
    $card_image->scaleImage(0, $card->get_height());
    $card_image->setImageFormat('bmp');

    $position = $main_table->cell_position($index);
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




# $table = new HorizontallyOverlappingTable(910, $cell->get_height() * 1, $cell);





// const LOAD_IMAGE_TIMEOUT = 5000;

// header("Content-type: image/" . getenv('IMAGE_EXT'));
// $img = load_card_image(40640057, LOAD_IMAGE_TIMEOUT);

// echo $img;
