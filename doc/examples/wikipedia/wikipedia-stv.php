<?php
require '../../../library.php';

$file = 'data/wikipedia-stv.blt';

$count = new DrooPHP\Count($file);
$method = new DrooPHP\Method\Wikipedia($count);

$method->run();

header('Content-Type: text/plain; charset=UTF-8');

print_r($method);
