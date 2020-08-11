<?php

use Http\Http;
use Game\DeckList;

require(__DIR__ . '/../vendor/autoload.php');






$response = new JsonErrorResponse();
$response->error('could not detect ...');

$result = $response->to_json(JSON_PRETTY_PRINT);

Http::header('Content-Type', 'application/json');
echo $result;



// $response = new JsonResponse();
// $response->meta('format', 'ydk');
// $response->data('decks', new DeckList());

// $result = $response->to_json(JSON_PRETTY_PRINT);

// Http::header('Content-Type', 'application/json');
// echo $result;








// use Game\Deck;
// use Game\DeckList;
// use Game\MainDeck;

// $dl = new DeckList();


// $time = microtime(true);
// $cnt  = 10000;

// for ($i=0; $i < $cnt; $i++) {
//     $dl->decks();
// }

// $diff = microtime(true) - $time;
// var_dump($diff);
// var_dump($diff / $cnt);

// var_dump(iterator_to_array($dl->decks()));


// $json = $dl->to_json();
// var_dump($json);
// var_dump(DeckList::from_json($json));





// exit;


// class Bar # extends Json\Serializable
// {
//     public $arr;

//     public function __construct()
//     {
//         $this->arr = 1; #[1,2,3];
//     }
// }

// class Foo extends Json\Serializable # Json\Serializable
// {
//     public Bar $bar;
//     public int $foo = 2;
//     protected array $arrayish = [1,23];

//     public function __construct(int $i = 0)
//     {
//         var_dump("ctor");

//         $this->bar = new Bar();
//         $this->foo = $i;
//     }

//     public function set_arr(array $array)
//     {
//         $this->arrayish = $array;
//     }

//     public function json_serialize(&$result): void
//     {
//         $result = [];
//         $decks  = [];

//         $main = [];
//         foreach ($this->arrayish as $code)
//             $main[] = $code;

//         $decks['main']   = $main;
//         $result['decks'] = $decks;
//     }

//     public function json_deserialize($value): void
//     {
//         $this->__construct(1000);
//         $this->set_arr($value['decks']['main']);
//     }
// }


// $foo = new Foo();
// // $foo->foo = 10000;
// $foo->set_arr([1,2,3,4]);

// $json = $foo->to_json();

// var_dump($foo);
// var_dump($json);

// $new_foo = Foo::from_json($json);

// var_dump($new_foo);
// var_dump($foo == $new_foo);



// $json = $foo->to_json();


// var_dump($foo);
// var_dump($json);

// $new_foo = Foo::from_json($json);
// var_dump($new_foo);

// var_dump($foo == $new_foo);
