<?php
/**
 * @file
 * Test setting up a count based on a ballot file.
 */

namespace DrooPHP;

class FileTest extends \PHPUnit_Framework_TestCase {

  public function testLoadElection() {
    $source = new Source\File([
      'filename' => __DIR__ . '/data/wikipedia-counting_stv.blt',
    ]);
    $election = $source->loadElection();
    $this->assertTrue($election instanceof ElectionInterface, 'Election loaded');
  }

}
