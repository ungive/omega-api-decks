<?php

require('../vendor/autoload.php');


use \Game\Card;
use \Game\CardList;
use \Game\DeckType;

use \Format\ParsedCard;
use \Format\ParsedCardList;

use \Utility\ListObject;





// $list = new CardList();

// $list[] = new Card(1, "Kuriboh", CardType::MAIN_DECK);
// $list[] = new Card(null, "lol");
// # $list[] = Card::with_name("Gaia", CardType::EXTRA_DECK);
// $list[] = Card::with_code(40640057, CardType::MAIN_DECK);

// var_dump($list);


// $card1 = ParsedCard::with_code(40640057, CardType::MAIN_DECK);
// $card2 = ParsedCard::with_code(40640058, CardType::MAIN_DECK);
// $card3 = ParsedCard::with_code(40640059, CardType::EXTRA_DECK);

// $parsed_cards = new ParsedCardList([
//     $card1, $card2, $card3
// ]);

// var_dump($parsed_cards);




// $main_deck_cards  = $parsed_cards->filter_deck_type(CardType::MAIN_DECK);
// $extra_deck_cards = $parsed_cards->filter_deck_type(CardType::EXTRA_DECK);

// var_dump($main_deck_cards);
// var_dump($extra_deck_cards);


// array_filter($list->array(), function ($card) {
//     return $card->
// });






// $api_url = getenv('CARD_API');
// $cards = json_decode(file_get_contents($api_url . '?id=' . '26202165,26202165,40640057,40640057'));
// var_dump($cards);



// $ydke = new YdkeFormatStrategy();
// $ygoprodeck = new YgoproDeckDataSource(getenv('YGOPRODECK_URL'));

// $deck_list = $ydke->decode($encoded);
// $cards = $deck_list->cards();

// $ygoprodeck->fetch_missing($main_deck);






// $card = new Card('Kuriboh');
// $card->location->set(CardLocation::MAIN | CardLocation::EXTRA);


// var_dump($card->location->has(CardLocation::MAIN));
// var_dump($card->location->has(CardLocation::EXTRA));
// var_dump($card->location->has(CardLocation::SIDE));

// var_dump(count($card->location));





