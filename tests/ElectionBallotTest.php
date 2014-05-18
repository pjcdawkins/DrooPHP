<?php
/**
 * @file
 * Tests for interactions between elections and ballots.
 */

namespace DrooPHP\Test;

use DrooPHP\Ballot;
use DrooPHP\Election;

class ElectionBallotTest extends \PHPUnit_Framework_TestCase {

  /**
   * @return array
   */
  protected function randomRanking() {
    return [1 => 19, 2 => 84, 3 => rand(1, 50)];
  }

  /**
   * Test adding ballots.
   */
  public function testBallots() {
    $election = new Election();
    $ranking1 = $this->randomRanking();
    $ballot1 = new Ballot($ranking1, 19);
    $election->addBallot($ballot1);
    $this->assertContains($ballot1, $election->getBallots());

    // Create a ballot with an identical ranking and try adding it to the
    // election.
    $ballot2 = new Ballot($ranking1, 84);
    $election->addBallot($ballot2);
    // The $ballot2 object should not have been added to the election. Instead,
    // $ballot2's value should have been added to $ballot1.
    $this->assertContains($ballot1, $election->getBallots());
    $this->assertNotContains($ballot2, $election->getBallots());
    $expected_value = 19 + 84;
    $this->assertEquals($expected_value, $ballot1->getValue());
    $this->assertEquals($expected_value, $election->getNumBallots());
    $this->assertEquals($expected_value, $election->getNumValidBallots());

    // Create a ballot with a different ranking and do the same.
    $ballot3 = new Ballot($this->randomRanking(), 12);
    $election->addBallot($ballot3);
    $this->assertContains($ballot3, $election->getBallots());
    $expected_value = 19 + 84 + 12;
    $this->assertEquals($expected_value, $election->getNumBallots());
    $this->assertEquals($expected_value, $election->getNumValidBallots());

    // Try the setBallots() method.
    $ballot4 = new Ballot($this->randomRanking());
    $ballot5 = new Ballot($this->randomRanking());
    $ballot6 = new Ballot($this->randomRanking());
    $ballots = [$ballot4, $ballot5, $ballot6];
    $total_value = rand(1, 10);
    $election->setBallots($ballots, $total_value);
    $this->assertEquals($ballots, $election->getBallots());
    $this->assertEquals($total_value, $election->getNumValidBallots());
  }

}
