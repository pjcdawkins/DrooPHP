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

class MethodTest extends \PHPUnit_Framework_TestCase {

  /**
   * Create a test election.
   *
   * @return ElectionInterface
   */
  public function getTestElection() {
    $election = new Election();
    $election->num_candidates = 2;
    $election->num_seats = 1;
    $election->addCandidate('Test candidate 1');
    $election->addCandidate('Test candidate 2');
    $election->ballots['1'] = new Ballot([1 => 1], rand(1, 5));
    $election->ballots['2 1'] = new Ballot([1 => 2, 2 => 1], rand(1, 5));
    return $election;
  }

  /**
   * Test that a method count can be run.
   */
  public function testRun() {
    $method = new Method\Wikipedia();
    $method->setElection($this->getTestElection());
    $result = $method->run();
    $this->assertTrue($result, 'Count runs');
  }

}
