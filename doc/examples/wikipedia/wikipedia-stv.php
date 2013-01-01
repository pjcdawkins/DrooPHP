<?php
require '../../../library.php';

$file = 'data/wikipedia-stv.blt';

$source = new DrooPHP\Source\File(array('filename' => $file));
$count = new DrooPHP\Count($source);
$method = new DrooPHP\Method\Wikipedia($count);

$method->run();

header('Content-Type: text/plain; charset=UTF-8');

print_r($method);
