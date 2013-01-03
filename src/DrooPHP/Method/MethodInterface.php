<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Method;

use DrooPHP\Count;

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
     * Run the count.
     */
    public function run();

}
