<?php

include 'config.php';


$source = [];
for ($i = 0; $i < 100; $i++) {
    $source[mt_rand(0, 999)] = mt_rand(0, 99999);
}
for ($i = 0; $i < 100; $i++) {
    $source[mt_rand(29000, 29999)] = mt_rand(0, 99999);
}


$m11 = memory_get_usage();
$s11 = microtime(true);

$array = [];
foreach ($source as $key => $value) {
    $array[$key] = $value;
}

print 'Count = ' . count($array) . PHP_EOL;

print "Elements:\n";
foreach ($array as $key => $value) {
    //print "$key => $value \n";
}


$s12 = microtime(true);
gc_collect_cycles();
$m12 = memory_get_usage();

print 'ARRAY:' . PHP_EOL;
print 'MEM:' . ($m12 - $m11) . PHP_EOL;
print 'TIME:' . ($s12 - $s11) . PHP_EOL;
