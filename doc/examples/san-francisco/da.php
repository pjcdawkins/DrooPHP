<?php
require '../../../library.php';

$file = 'data/SanFrancisco-DA-2011.blt';

$options = array(
    'allow_skipped' => 1,
    'allow_repeat' => 1,
    'allow_equal' => 1,
);

$source = new DrooPHP\Source\File(array('filename' => $file) + $options);
$count = new DrooPHP\Count($source, $options);
$method = new DrooPHP\Method\Wikipedia($count);
$method->run();

header('Content-Type: text/plain; charset=UTF-8');

print_r($method);
