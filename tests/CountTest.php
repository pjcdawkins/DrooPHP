<?php
/**
 * @file
 * Tests for an entire election count.
 */

namespace DrooPHP\Test;

use DrooPHP\Count;
use DrooPHP\Formatter\Csv;
use DrooPHP\Method\Wikipedia;
use DrooPHP\Source\File;

class CountTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test that a count can be run.
   */
  public function testRun() {
    $source = new File(['filename' => __DIR__ . '/data/wikipedia-counting_stv.blt']);
    $count = new Count([
      'source' => $source,
    ]);
    $result = $count->run();
    $this->assertTrue(strlen($result) > 0, 'Count returns data');
  }

}
