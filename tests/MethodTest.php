<?php
/**
 * @file
 * Tests for vote counting methods.
 */

namespace DrooPHP\Test;

use DrooPHP\Ballot;
use DrooPHP\Candidate;
use DrooPHP\Election;
use DrooPHP\ElectionInterface;
use DrooPHP\Method;

class MethodTest extends \PHPUnit_Framework_TestCase {

  /**
   * Create a test election.
   *
   * @return ElectionInterface
   */
  public function getTestElection() {
    $election = new Election();
    $election->addCandidate(new Candidate('Test candidate 1', 1));
    $election->addCandidate(new Candidate('Test candidate 2', 2));
    $election->addBallot(new Ballot([1 => 1], rand(1, 5)));
    $election->addBallot(new Ballot([1 => 2, 2 => 1], rand(1, 5)));
    return $election;
  }

  /**
   * Test that a method count can be run.
   */
  public function testRun() {
    $method = new Method\Stv();
    $method->setElection($this->getTestElection());
    $result = $method->run();
    $this->assertTrue($result, 'Count runs successfully');
  }

}
