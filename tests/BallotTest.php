<?php
/**
 * @file
 * Tests related to candidates.
 */

namespace DrooPHP\Test;

use DrooPHP\Ballot;

class BallotTest extends \PHPUnit_Framework_TestCase {

  public function testLevels() {
    $ranking = [
      1 => [rand(0, 24)],
      2 => [rand(25, 49)],
      3 => [rand(50, 74), rand(75, 99)],
      4 => [rand(100, 124)],
      5 => [rand(125, 149)],
    ];
    $value = rand(1, 10);
    $ballot = new Ballot($ranking, $value);
    $this->assertEquals($value, $ballot->getValue());
    for ($i = 1; $i <= 5; $i++) {
      $this->assertEquals($ranking[$i], $ballot->getPreference($i), "Ranking $i correct");
    }
    $ballot->setLastUsedLevel(1);
    $this->assertEquals($ranking[2], $ballot->getNextPreference());
    $ballot->setLastUsedLevel(2);
    $this->assertEquals($ranking[3], $ballot->getNextPreference());
    $ballot->setLastUsedLevel(2, TRUE);
    $this->assertEquals($ranking[5], $ballot->getNextPreference());
    $ballot->setLastUsedLevel(1, TRUE);
    $this->assertEquals([], $ballot->getNextPreference());
  }

}
