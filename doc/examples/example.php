<?php
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require '../../DrooPHP.php';
DrooPHP::init();

$file = 'OpenSTV/SanFrancisco-Sheriff-2011.blt';
$file = 'OpenSTV/SanFrancisco-Mayor-2011.blt';
$file = 'simple.blt';

$options = array(
  'method' => 'DrooPHP_Method_Ers97',
  'allow_skipped' => 1,
  'allow_invalid' => 1,
);

$start = microtime(TRUE);
$count = new DrooPHP_Count($file, $options);
echo '<div>Execution time of DrooPHP_Count::__construct(): ' . number_format(microtime(TRUE) - $start, 3) . 's.</div>';
echo '<div>Memory usage: ' . number_format(memory_get_usage()) . ' bytes.</div>';

$start = microtime(TRUE);
$method = new DrooPHP_Method_Ers97($count);
$method->run();
echo '<div>Execution time of DrooPHP_Method::run(): ' . number_format(microtime(TRUE) - $start, 3) . 's.</div>';
echo '<div>Memory usage: ' . number_format(memory_get_usage()) . ' bytes.</div>';
echo '<pre>' . print_r($method, true) . '</pre>';
