<?php



$generate = function () {
    for ($i=0; $i < 10; $i++) {
        yield $i;
    }
};

$generator = $generate();

$a = [];

$a = array_merge($a, iterator_to_array($generator));

var_dump($a);
var_dump((array)($generate()));

// var_dump(iterator_to_array($generator));





// $s = '';

// $r = unpack('V', $s);

// var_dump($r);




// $a = array();

// $a['zoo'] = 'bra';
// $a['funky'] = 'bass';
// $a['gooey'] = 'stuff';
// $a['hurl'] = 'it';
// $a['inject'] = 'something';

// foreach ($a as $key => $value) {
//     var_dump($key . ': ' . $value);
// }

// var_dump($a);
