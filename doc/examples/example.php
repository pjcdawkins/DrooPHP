<?php
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require '../../DrooPHP.php';
DrooPHP::init();

$options = array(
  'method' => 'DrooPHP_Method_Ers97',
);

//$count = new DrooPHP_Count('OpenSTV/SanFrancisco-Sheriff-2011.blt', $options);
$count = new DrooPHP_Count('simple.blt', $options);

header('Content-Type: text/plain; charset=UTF-8');


$method = new DrooPHP_Method_Ers97($count);

$method->run();

var_dump($method);
