<?php
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require '../../../DrooPHP.php';
DrooPHP::init();

$file = 'data/wikipedia-stv.blt';

$count = new DrooPHP_Count($file);
$method = new DrooPHP_Method_Wikipedia($count);

$method->run();

echo '<pre>' . print_r($method, true) . '</pre>';
