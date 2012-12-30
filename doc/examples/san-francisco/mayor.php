<?php
require '../../../library.php';

$file = 'data/SanFrancisco-Mayor-2011.blt';

$options = array(
    'allow_skipped' => 1,
    'allow_repeat' => 1,
    'allow_equal' => 1,
);

$count = new DrooPHP\Count($file, $options);

$method = new DrooPHP\Method\Wikipedia($count);
$method->run();

header('Content-Type: text/plain; charset=UTF-8');

print_r($method);
