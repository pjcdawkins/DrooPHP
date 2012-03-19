<?php
require '../../DrooPHP.php';
DrooPHP::init();

$sheriff = new DrooPHP_Election('OpenSTV/SanFrancisco-Sheriff-2011.blt');

header('Content-Type: text/plain; charset=UTF-8');
var_dump($sheriff);
