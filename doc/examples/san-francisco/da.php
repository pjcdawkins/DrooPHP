<?php
require '../../../library.php';

$file = 'data/SanFrancisco-DA-2011.blt';

$options = array(
    'filename' => $file,
    'allow_skipped' => 1,
    'allow_repeat' => 1,
    'allow_equal' => 1,
);

$count = new DrooPHP\Count(
    new DrooPHP\Source\File(),
    new DrooPHP\Method\Wikipedia(),
    $options
);

$output = $count->run();

header('Content-Type: text/plain; charset=UTF-8');

print_r($output);
