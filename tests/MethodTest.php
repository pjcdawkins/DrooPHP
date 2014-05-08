<?php
/**
 * @file
 * Tests for vote counting methods.
 */

namespace DrooPHP\Test;

use DrooPHP\Ballot;
use DrooPHP\Election;
use DrooPHP\ElectionInterface;
use DrooPHP\Method;
use DrooPHP\ResultInterface;

class MethodTest extends \PHPUnit_Framework_TestCase {

  /**
   * Create a test election.
   *
   * @return ElectionInterface
   */
  public function getTestElection() {
    $election = new Election();
    $election->setNumCandidates(2);
    $election->setNumSeats(1);
    $election->addCandidate('Test candidate 1');
    $election->addCandidate('Test candidate 2');
    $election->addBallot(new Ballot([1 => 1], rand(1, 5)), '1');
    $election->addBallot(new Ballot([1 => 2, 2 => 1], rand(1, 5)), '2 1');
    return $election;
  }

  /**
   * Test that a method count can be run.
   */
  public function testRun() {
    $method = new Method\Wikipedia();
    $method->setElection($this->getTestElection());
    $result = $method->run();
    $this->assertTrue($result instanceof ResultInterface, 'Count runs');
  }

}
