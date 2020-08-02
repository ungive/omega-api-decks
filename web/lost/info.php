<?php

require '../vendor/autoload.php';

use \Format\FormatStrategy;
use \Format\FormatEncodeStrategy;
use \Format\FormatDecodeStrategy;
use \Format\YdkeFormatStrategy;
use \Format\OmegaFormatStrategy;

use \Format\ParsedCardList;

use \Game\Data\YgoproDeckDataSource;

use \Game\Deck;
use \Game\DeckList;
use \Game\DeckType;
use \Game\CardList;




## omega
# $omega_input = "0+a6LjWfEYbv/L/MAMIXps0AY4kjoiww/PbQdlYYFuz7zgDDKmaXWGB4zsmPjCC8uMSeGYRfys5kheHgpcuZQXj3GXs4XnDhIQscP7oGx/ll7xlguPCSLrM1cx1L/+bXjBYbk1k0uaWYg753MQcD8Ub3TWD8MGIuGIPsBNkBcgMA";

$omega_input = "0+a6LjWfEYbv/L/MAMIXps0AY4kjoiww/PbQdlYYFuz7zgDDKmaXWGB4zsmPjCC8uMSeGYRfys5kheHgpcuZQXj3GXs4XnDhIQscP7oGx/ll7xlguPCSLrM1cx1L/+bXjBYbk1k0uaWYg753MQcD8Ub3TWD8MGIuGIPsBNkBAA==";

## ydke
$ydke_input = "ydke://EUKKAwrmpwEK5qcBR5uPAEebjwBHm48AvadvAfx5vAKzoLECTkEDAE5BAwBOQQMAfjUBBUwyuADDhdcAnNXGA/ZJ0ACmm/QBPqRxAT6kcQE+pHEBVhgUAVYYFAFWGBQBZOgnA2ToJwNk6CcDIkiZACJImQAiSJkAdgljAnYJYwJ2CWMCVOZcAVTmXAF9e0AChKFCAYShQgGEoUIBPO4FAzzuBQM=!y7sdAIoTdQOKE3UDwLXNA9EgZgUNUFsFtWJvAqRbfAOkW3wDlk8AAoVAsQKA9rsBlI9dAQdR1QE5ySIF!URCDA1EQgwNREIMDI9adAiPWnQJvdu8Ab3bvANcanwHXGp8B1xqfASaQQgMmkEIDJpBCA0O+3QBDvt0A!";



$strategy = new OmegaFormatStrategy(); # new YdkeFormatStrategy(); #
$repository = new YgoproDeckDataSource(getenv('YGOPRODECK_URL'));
$input = $omega_input;


$parsed = $strategy->decode($input);



$deck_list = new DeckList();
$invalid = new ParsedCardList();

foreach ($parsed as $card) {
    if (!$card->is_valid()) {
        $invalid[] = $card;
        continue;
    }

    $deck_type = $card->deck_types->get();
    $deck_list->get($deck_type)->add($card->to_card());
}


#####
$invalid_count = count($invalid);
var_dump("{$invalid_count} invalid cards");
#####






$result = $repository->get_cards_by_code(...$invalid->card_codes());


foreach ($invalid as $card) {
    $code = $card->code;

    if (!isset($result[$code]))
        throw new Exception("card not found");

    $data = $result[$code];
    $card->deck_types->set($data->deck_type);
    $deck_list->get($data->deck_type)->add($card->to_card());
}



var_dump($deck_list);








// $encoded_omega = try_encoder($omega, $deck_list);
// var_dump($omega_input);
// var_dump($encoded_omega);



// var_dump("################");
// var_dump($invalid);
// var_dump($deck_list);




# $reencoded = $ydke->encode($deck_list);

# var_dump($encoded);
# var_dump($reencoded);

# $ids = implode(',', $deck_list);
# $cards = json_decode(file_get_contents(getenv('CARD_API') . '?id=' . $ids));
# var_dump($ids);

exit;


# phpinfo();



