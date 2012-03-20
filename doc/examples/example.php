<?php
ini_set('display_errors', TRUE);
require '../../DrooPHP.php';
DrooPHP::init();

$count = new DrooPHP_Count('OpenSTV/SanFrancisco-Sheriff-2011.blt');

header('Content-Type: text/plain; charset=UTF-8');
var_dump($count);
