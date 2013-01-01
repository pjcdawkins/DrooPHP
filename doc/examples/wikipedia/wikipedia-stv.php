<?php
require '../../../library.php';

$file = 'data/wikipedia-stv.blt';

$count = new DrooPHP\Count(array(
    'source' => new DrooPHP\Source\File(array(
        'filename' => $file,
    )),
    'method' => 'Wikipedia',
));

$output = $count->run();

header('Content-Type: text/plain; charset=UTF-8');

print_r($output);
