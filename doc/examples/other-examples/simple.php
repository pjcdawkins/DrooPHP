<?php
require '../../../DrooPHP.php';
DrooPHP::init();

$file = 'data/simple.blt';

$count = new DrooPHP_Count($file);
$method = new DrooPHP_Method_Wikipedia($count);

$method->run();

echo '<pre>' . print_r($method, true) . '</pre>';
