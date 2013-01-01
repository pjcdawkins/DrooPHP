<?php
require '../../../library.php';

$file = 'data/SanFrancisco-Mayor-2011.blt';

$options = array(
    'allow_skipped' => 1,
    'allow_repeat' => 1,
    'allow_equal' => 1,
);

$count = new DrooPHP\Count($options + array(
    'source' => new DrooPHP\Source\File($options + array(
        'filename' => $file,
    )),
));

$output = $count->run();

header('Content-Type: text/plain; charset=UTF-8');

print_r($output);
