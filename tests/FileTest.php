<?php
/**
 * @file
 * Tests for reading ballot files.
 */

namespace DrooPHP\Test;

use DrooPHP\ElectionInterface;
use DrooPHP\Source;

class FileTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test that an election can be loaded successfully from a ballot file.
   */
  public function testLoad() {
    $source = new Source\File([
      'filename' => __DIR__ . '/data/wikipedia-counting_stv.blt',
      'cache_enable' => FALSE,
    ]);
    $election = $source->loadElection();
    $this->assertTrue($election instanceof ElectionInterface);
    $this->assertSame($election->getTitle(), "Wikipedia example from article 'Counting Single Transferable Votes'", 'Election title');
    $this->assertSame($election->getNumSeats(), 2, 'Number of vacancies');
    $this->assertCount(4, $election->getCandidates(), 'Number of candidates (calculated)');
    $this->assertSame($election->getNumBallots(), 57, 'Number of ballots');
    $this->assertSame($election->getNumValidBallots(), 57, 'Number of valid ballots');
    $this->assertSame($election->getNumInvalidBallots(), 0, 'Number of invalid ballots');
    $candidate_names = [];
    foreach ($election->getCandidates() as $candidate) {
      $candidate_names[] = $candidate->getName();
    }
    $expected_names = ['Andrea', 'Brad', 'Carter', 'Delilah'];
    $this->assertEquals($candidate_names, $expected_names, 'Candidate names');
  }

}
