<?php
/**
 * @package DrooPHP
 * @author Patrick Dawkins <pjcdawkins@gmail.com>
 */

namespace DrooPHP;

/**
 * Container for a ballot, i.e. an individual "ballot paper", cast by a single
 * voter.
 */
class Ballot
{

    /** @var array */
    public $ranking;

    /** @var int|float */
    public $value;

    /**
     * Constructor.
     *
     * @param array $ranking
     *    The ranking, expressed as an array of candidate IDs keyed by their
     *    preference level (e.g. the second preference candidate is keyed by 2).
     *
     * @param int|float $value
     *    The value of this ballot (default: 1).
     */
    public function __construct(array $ranking, $value = 1)
    {
        $this->ranking = $ranking;
        $this->value = $value;
    }

}
