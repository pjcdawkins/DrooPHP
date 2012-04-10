<?php
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require '../../DrooPHP.php';
DrooPHP::init();

$options = array(
  'method' => 'DrooPHP_Method_Ers97',
  //'allow_repeat' => 1,
  //'allow_equal' => 1,
  'allow_skipped' => 1,
  'allow_invalid' => 1,
);

$start = microtime(TRUE);

$count = new DrooPHP_Count('OpenSTV/SanFrancisco-Sheriff-2011.blt', $options);

echo '<div>Execution time of DrooPHP_Count::__construct(): ' . number_format(microtime(TRUE) - $start, 2) . 's.</div>';
echo '<div>Peak memory usage: ' . number_format(memory_get_peak_usage()) . ' bytes.</div>';
echo '<div>Memory usage: ' . number_format(memory_get_usage()) . ' bytes.</div>';

echo '<pre>' . print_r($count, true) . '</pre>';

//var_dump($count);

//$method = new DrooPHP_Method_Ers97($count);

//$method->run();

//var_dump($method);
