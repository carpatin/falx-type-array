<?php

include 'config.php';

use Falx\Type\Lists\DoublyLinked;

$m11 = memory_get_usage(true);
$s11 = microtime(true);

$list = new DoublyLinked();

for ($i = 0; $i < 10000; $i++) {
    $list->push($i);
}

$s12 = microtime(true);
gc_collect_cycles();
$m12 = memory_get_usage(true);

print 'OWN:' . PHP_EOL;
print 'MEM:' . ($m12 - $m11) . PHP_EOL;
print 'TIME:' . ($s12 - $s11) . PHP_EOL;

$m21 = memory_get_usage(true);
$s21 = microtime(true);

$list2 = new SplDoublyLinkedList();

for ($i = 0; $i < 10000; $i++) {
    $list2->push($i);
}

$s22 = microtime(true);
gc_collect_cycles();
$m22 = memory_get_usage(true);

print 'SPL:' . PHP_EOL;
print 'MEM:' . ($m22 - $m21) . PHP_EOL;
print 'TIME:' . ($s22 - $s21) . PHP_EOL;
