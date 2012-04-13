<?php
require '../../../library.php';

$file = 'data/wikipedia-counting_stv.blt';

$count = new DrooPHP_Count($file);
$method = new DrooPHP_Method_Wikipedia($count);

$method->run();

header('Content-Type: text/plain; charset=UTF-8');

print_r($method);
