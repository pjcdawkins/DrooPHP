<?php
/**
 * @file
 * Tests for an entire election count.
 */

namespace DrooPHP\Test;

use DrooPHP\Count;
use DrooPHP\ResultInterface;
use DrooPHP\Source\File;

class CountTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test that a count can be run.
   */
  public function testRun() {
    $source = new File([
      'filename' => __DIR__ . '/data/wikipedia-counting_stv.blt',
      'cache_enable' => FALSE,
    ]);
    $count = new Count(['source' => $source]);
    $result = $count->getResult();
    $this->assertTrue($result instanceof ResultInterface, 'Count runs successfully');
    $elected = $result->getElected();
    $names = array();
    foreach ($elected as $candidate) {
      $names[] = $candidate->getName();
    }
    $expected = array('Andrea', 'Carter');
    $this->assertEquals($expected, $names, 'Correct people elected');
  }

  /**
   * Test that formatted output is produced.
   */
  public function testOutput() {
    $source = new File([
      'filename' => __DIR__ . '/data/wikipedia-counting_stv.blt',
      'cache_enable' => FALSE,
    ]);
    $count = new Count(['source' => $source, 'formatter' => 'Html']);
    $output = $count->getOutput();
    $this->assertNotEmpty($output, 'HTML output obtained');
    $count->setOption('formatter', 'text');
    $output = $count->getOutput();
    $this->assertNotEmpty($output, 'Text output obtained');
    $count->setOption('formatter', 'CSV');
    $output = $count->getOutput();
    $this->assertNotEmpty($output, 'CSV output obtained');
    $count->setOption('formatter', 'TSV');
    $output = $count->getOutput();
    $this->assertNotEmpty($output, 'TSV output obtained');
  }

}
