<?php
/**
 * @file
 * Tests related to candidates.
 */

namespace DrooPHP\Test;

use DrooPHP\Candidate;

class CandidateTest extends \PHPUnit_Framework_TestCase {

  public function testState() {
    $candidate = new Candidate('Test candidate', 1);
    $this->assertEquals(Candidate::STATE_HOPEFUL, $candidate->getState());
    $candidate->setState(Candidate::STATE_ELECTED);
    $this->assertEquals(Candidate::STATE_ELECTED, $candidate->getState());
  }

  public function testVotes() {
    $candidate = new Candidate('Test candidate', 1);
    $candidate->addVotes(10);
    $this->assertEquals(10, $candidate->getVotes());
    $candidate->addVotes(0.5);
    $this->assertEquals(10.5, $candidate->getVotes());
    $candidate->addVotes(-5);
    $this->assertEquals(5.5, $candidate->getVotes());

    $candidate2 = new Candidate('Test candidate 2', 2);
    $candidate->transferVotes(5, $candidate2);
    $this->assertEquals(5, $candidate2->getVotes());
    $this->assertEquals(0.5, $candidate->getVotes());
  }

  public function testSurplus() {
    $candidate = new Candidate('Test candidate', 1);
    $candidate->setSurplus(10);
    $this->assertEquals(10, $candidate->getSurplus());
    $candidate->setSurplus(-5, TRUE);
    $this->assertEquals(5, $candidate->getSurplus());
    $candidate->setSurplus(5.75, TRUE);
    $this->assertEquals(10.75, $candidate->getSurplus());
  }

}
