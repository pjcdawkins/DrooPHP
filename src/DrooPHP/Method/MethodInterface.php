<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP\Method;

/**
 * Interface for a vote counting method.
 */
interface MethodInterface
{

    /**
     * Constructor.
     */
    public function __construct(array $options = array());

    /**
     * Run the count.
     */
    public function run();

}
