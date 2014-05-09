<?php
/**
 * @file
 * Tests for an entire election count.
 */

namespace DrooPHP\Test;

use DrooPHP\CandidateInterface;
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
    $count = new Count(['source' => $source]);
    $result = $count->run();
    $this->assertTrue(strlen($result) > 0, 'Count returns data');
    $elected = $count->getMethod()->getElection()->getCandidates(CandidateInterface::STATE_ELECTED);
    $names = array();
    foreach ($elected as $candidate) {
      $names[] = $candidate->getName();
    }
    $expected = array('Andrea', 'Carter');
    $this->assertEquals($expected, $names, 'Correct people elected');
  }

}
