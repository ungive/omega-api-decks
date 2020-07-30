<?php

require '../vendor/autoload.php';

use \Format\FormatStrategy;
use \Format\YdkeFormatStrategy;
use \Format\OmegaFormatStrategy;

use \Format\ParsedCardList;

use \Game\Deck;
use \Game\DeckList;
use \Game\DeckType;
use \Game\CardList;




## ydke
$encoded = "ydke://EUKKAwrmpwEK5qcBR5uPAEebjwBHm48AvadvAfx5vAKzoLECTkEDAE5BAwBOQQMAfjUBBUwyuADDhdcAnNXGA/ZJ0ACmm/QBPqRxAT6kcQE+pHEBVhgUAVYYFAFWGBQBZOgnA2ToJwNk6CcDIkiZACJImQAiSJkAdgljAnYJYwJ2CWMCVOZcAVTmXAF9e0AChKFCAYShQgGEoUIBPO4FAzzuBQM=!y7sdAIoTdQOKE3UDwLXNA9EgZgUNUFsFtWJvAqRbfAOkW3wDlk8AAoVAsQKA9rsBlI9dAQdR1QE5ySIF!URCDA1EQgwNREIMDI9adAiPWnQJvdu8Ab3bvANcanwHXGp8B1xqfASaQQgMmkEIDJpBCA0O+3QBDvt0A!";

$ydke = new YdkeFormatStrategy();

$parsed = $ydke->decode($encoded);


# TODO: deck list from list of deck types: [ MAIN, EXTRA, SIDE ]

// $deck_list = [
//     DeckType::MAIN => new Deck(DeckType::MAIN),
//     DeckType::EXTRA => new Deck(DeckType::EXTRA),
//     DeckType::SIDE => new Deck(DeckType::SIDE)
// ];



$deck_list = new DeckList();

var_dump($deck_list[DeckType::MAIN]);

$deck_list[DeckType::MAIN] = new Deck();

var_dump($deck_list);




exit;



$invalid = new ParsedCardList();

foreach ($parsed as $card) {
    if (!$card->is_valid())
        $invalid_cards[] = $card;

    $deck_type = $card->deck_types->get();

    $deck_list[DeckType::MAIN]->

    // if ($card->deck_types->has(DeckType::SIDE))
    //     $card->deck_types->set(DeckType::UNKNOWN);

    $deck_list[$deck_type]->add($card->to_card());
}


var_dump($invalid);
var_dump($deck_list);



# $reencoded = $ydke->encode($deck_list);

# var_dump($encoded);
# var_dump($reencoded);

# $ids = implode(',', $deck_list);
# $cards = json_decode(file_get_contents(getenv('CARD_API') . '?id=' . $ids));
# var_dump($ids);

exit;


# phpinfo();


## omega
$encoded = "0+a6LjWfEYbv/L/MAMIXps0AY4kjoiww/PbQdlYYFuz7zgDDKmaXWGB4zsmPjCC8uMSeGYRfys5kheHgpcuZQXj3GXs4XnDhIQscP7oGx/ll7xlguPCSLrM1cx1L/+bXjBYbk1k0uaWYg753MQcD8Ub3TWD8MGIuGIPsBNkBcgMA";

$ydke = new OmegaFormatStrategy();

function unpack_inc(string $format, string &$data)
{
    $unpacked = unpack($format, $data);
    $data = substr($data, count($unpacked));
    return $unpacked;
}



$deflated = base64_decode($encoded);
$raw = gzinflate($deflated);

$main_count = unpack_inc('C', $raw);
$side_count = unpack_inc('C', $raw);

$main_deck = [];
$extra_deck = [];
$side_deck = [];

// for ($i = 0; $i < $main_count; ++$i)
//     $main_deck[] = unpack('');


var_dump($main_count);
var_dump($side_count);
exit;
