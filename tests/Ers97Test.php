<?php
/**
 * @file
 * Tests related to the ERS97 counting method.
 */

namespace DrooPHP\Test;

use DrooPHP\Ballot;
use DrooPHP\Candidate;
use DrooPHP\Count;
use DrooPHP\Election;
use DrooPHP\Method\Ers97;
use PHPUnit\Framework\TestCase;

class Ers97Test extends TestCase {

  public function testQuota() {
    $election = new Election();
    $election->setNumSeats(1);
    $election->addCandidate(new Candidate('Test candidate 1', 1));
    $election->addCandidate(new Candidate('Test candidate 2', 2));
    $election->addBallot(new Ballot([1], 40));
    $election->addBallot(new Ballot([2], 60));

    $method = new Ers97();
    $method->setElection($election);

    $this->assertEquals(50, $method->getQuota());
    $election->addBallot(new Ballot([1, 2], 4));
    $this->assertEquals(50, $method->getQuota());
    $this->assertEquals(52, $method->getQuota(TRUE));
    $election->addBallot(new Ballot([1]));
    $this->assertEquals(52.5, $method->getQuota(TRUE));
    $election->setNumSeats(2);
    $election->addBallot(new Ballot([2, 1], 2));
    $this->assertEquals(35.67, $method->getQuota(TRUE));
    $election->addBallot(new Ballot([2, 1], 1));
    $this->assertEquals(36, $method->getQuota(TRUE));
  }

  public function testCount() {
    $count = new Count(['method' => 'Ers97', 'source' => 'File']);
    $count->getSource()->setOptions([
      'filename' => __DIR__ . '/data/wikipedia-counting_stv.blt',
    ]);
    $result = $count->getResult();
    $elected = $result->getElected();
    $names = array();
    foreach ($elected as $candidate) {
      $names[] = $candidate->getName();
    }
    // This result would be given from pretty much any counting method, so it is
    // not ERS97 specific. However, at this stage it is helpful to ensure that
    // the ERS97 method is giving vaguely accurate results.
    $expected = array('Andrea', 'Carter');
    $this->assertEquals($expected, $names, 'Correct people elected');
  }

}
