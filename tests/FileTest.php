<?php
/**
 * @file
 * Tests for reading ballot files.
 */

namespace DrooPHP;

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
    $this->assertSame($election->title, "Wikipedia example from article 'Counting Single Transferable Votes'", 'Election title');
    $this->assertSame($election->num_seats, 2, 'Number of vacancies');
    $this->assertSame($election->num_candidates, 4, 'Number of candidates');
    $this->assertCount(4, $election->candidates);
    $this->assertSame($election->num_valid_ballots, 57, 'Number of ballots');
    $candidate_names = [];
    foreach ($election->candidates as $candidate) {
      $candidate_names[] = $candidate->name;
    }
    $expected_names = ['Andrea', 'Brad', 'Carter', 'Delilah'];
    $this->assertEquals($candidate_names, $expected_names, 'Candidate names');
  }

}
