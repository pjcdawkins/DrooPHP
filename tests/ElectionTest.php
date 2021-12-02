<?php
/**
 * @file
 * Basic tests for the Election class.
 */

namespace DrooPHP\Test;

use DrooPHP\Election;
use PHPUnit\Framework\TestCase;

class ElectionTest extends TestCase {

  public function testTitle() {
    $election = new Election();
    $title = 'A test election title ' . rand(1, 100);
    $this->assertEquals('', $election->getTitle(), 'Default title is empty string');
    $election->setTitle($title);
    $this->assertEquals($title, $election->getTitle(), 'Set title once');
    $title = 'A test election title ' . rand(1, 100);
    $election->setTitle($title);
    $this->assertEquals($title, $election->getTitle(), 'Set title again');
  }

  public function testNumSeats() {
    $election = new Election();
    $this->assertEquals(1, $election->getNumSeats(), 'Default number of seats is 1');
    $num_seats = rand(1, 100);
    $election->setNumSeats($num_seats);
    $this->assertEquals($num_seats, $election->getNumSeats(), 'Set num seats once');
    $num_seats = rand(1, 100);
    $election->setNumSeats($num_seats);
    $this->assertEquals($num_seats, $election->getNumSeats(), 'Set num seats again');
  }

}
