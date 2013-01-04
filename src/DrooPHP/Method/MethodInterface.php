<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Method;

use DrooPHP\Count;
use DrooPHP\Election;

/**
 * Interface for a vote counting method.
 */
interface MethodInterface
{

    /**
     * Constructor.
     */
    public function __construct(Count $count);

    /**
     * Load in the election.
     *
     * @param Election $election
     *
     * @return self
     */
    public function setElection(Election $election);

    /**
     * Run the count.
     */
    public function run();

}
