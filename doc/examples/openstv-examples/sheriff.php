<?php
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require '../../../DrooPHP.php';
DrooPHP::init();

$file = 'openstv-data/SanFrancisco-Sheriff-2011.blt';

$options = array(
  'allow_skipped' => 1,
  'allow_repeat' => 1,
  'allow_equal' => 1,
);

$start = microtime(TRUE);
$count = new DrooPHP_Count($file, $options);
echo '<p>Execution time of <code>DrooPHP_Count::__construct()</code>: <var>' . number_format(microtime(TRUE) - $start, 3) . 's</var>.</p>';

$start = microtime(TRUE);
$method = new DrooPHP_Method_Wikipedia($count);
$method->run();
echo '<p>Execution time of <code>DrooPHP_Method::run()</code>: <var>' . number_format(microtime(TRUE) - $start, 3) . 's</var>.</p>';

echo '<p>Peak memory usage: <var>' . number_format(memory_get_peak_usage()) . ' bytes</var>.</p>';

echo '<pre>' . print_r($method, true) . '</pre>';
