<?php

include 'config.php';

use Falx\Type\Arrays\ElasticArray;

// test using the default sized inner fixed : 10

$array = new ElasticArray();

$array[0] = 'banana';
$array[11] = 'apple';
$array[44] = 'grapes';
$array[1] = 'kiwi';
$array[11] = 'pears';
$array[1000] = 'watermelon';
unset($array[44]);

print 'Count = ' . count($array) . PHP_EOL;
print 'Size = ' . $array->size() . PHP_EOL;

print "Elements:\n";
foreach ($array as $key => $value) {
    print "$key => $value \n";
}


print str_repeat('=', 50) . PHP_EOL;

// Same test but with inner fixed arrays of custom size : 5

$array = new ElasticArray(5);

$array[0] = 'banana';
$array[11] = 'apple';
$array[44] = 'grapes';
$array[1] = 'kiwi';
$array[11] = 'pears';
$array[1000] = 'watermelon';
unset($array[44]);

print 'Count = ' . count($array) . PHP_EOL;
print 'Size = ' . $array->size() . PHP_EOL;

print "Elements:\n";
foreach ($array as $key => $value) {
    print "$key => $value \n";
}
