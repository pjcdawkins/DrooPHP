<?php
/**
 * @file
 * Tests for the Election class.
 */

namespace DrooPHP\Test;

use DrooPHP\Candidate;
use DrooPHP\CandidateInterface;
use DrooPHP\Election;

class ElectionTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test getCandidate() and getCandidates() methods.
   */
  public function testGetCandidates() {
    $election = new Election();
    $this->assertEquals([], $election->getCandidates());
    $name = 'Test candidate';
    $id = rand(1, 50);
    $candidate = new Candidate($name, $id);
    $election->addCandidate($candidate);
    $this->assertEquals($candidate, $election->getCandidate($id));
    $this->assertArrayHasKey($id, $election->getCandidates());
    $candidate->setState(CandidateInterface::STATE_DEFEATED);
    $this->assertEquals([], $election->getCandidates(CandidateInterface::STATE_HOPEFUL));
    $this->assertEquals([$id => $candidate], $election->getCandidates(CandidateInterface::STATE_DEFEATED));
  }

  /**
   * Test checking for duplicates in the addCandidate() method.
   *
   * @expectedException \DrooPHP\Exception\UsageException
   */
  public function testDuplicateCandidate() {
    // Create three candidates, two of them with the same ID.
    $candidate1 = new Candidate('Test candidate 1', 19);
    $candidate2 = new Candidate('Test candidate 2', 87);
    $candidate3 = new Candidate('Test candidate 2', 19);
    $election = new Election();
    $election->addCandidate($candidate1);
    $election->addCandidate($candidate2);
    // Two candidates with the same ID cannot be added to the same election. So
    // this should throw an exception as specified in the annotation.
    $election->addCandidate($candidate3);
  }

}
