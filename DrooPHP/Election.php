<?php
/**
 * @file
 *   DrooPHP_Election class.
 * @package
 *   DrooPHP
 */

/**
 * @class
 *   DrooPHP_Election
 *   Main class for an election, containing an election profile, options, and
 *   count methods.
 */
class DrooPHP_Election {

  /**
   * The name of the BLT file.
   *
   * @var string
   */
  public $filename;

  /**
   * The seekable file resource handle (output of fopen).
   *
   * @var resource
   */
  public $file_handle;

  /** @var DrooPHP_Election_Profile */
  public $profile;

  /** @var array */
  public $options = array();

  /**
   * Constructor: initiate a count by loading a BLT file.
   *
   * @todo allow passing in a complete profile instead of a file, perhaps.
   */
  public function __construct($filename, $options = array()) {
    $this->loadOptions($options);
    if (file_exists($filename) && is_readable($filename)) {
      $this->filename = $filename;
      $this->file_handle = fopen($filename, 'r');
    }
    else {
      throw new Droop_Exception('The file does not exist or cannot be read.');
    }
    $this->profile = new DrooPHP_Election_Profile;
    $this->parse();
  }

  /**
   * Set up options for this election.
   *
   * Possible options:
   *   ron => The name of the Re-Open Nominations candidate, if there is one.
   *
   * @param array $options
   */
  public function loadOptions(array $options) {
    $options = array_merge($this->_getDefaultOptions(), $options);

    // 'ron' => TRUE is equivalent to 'ron' => 'RON'
    if ($options['ron'] === TRUE) {
      $options['ron'] = 'RON';
    }

    $this->options = $options;
  }

  /**
   * Parse the BLT file to create the election profile.
   *
   * @todo get around performance vs format limitations (requiring new lines and not supporting multiline comments)
   */
  public function parse() {
    // $i represents the line number, excluding comments and blank lines.
    $i = 0;
    try {
      while (($line = fgets($this->file_handle)) !== FALSE) {
        // Remove comments (starting with # or // until the end of the line).
        $line = preg_replace('/(\x23|\/\/).*/', '', $line);
        // Skip blank lines.
        $line = trim($line);
        if (!strlen($line)) {
          continue;
        }
        // Increment $i now that the invalid lines have been skipped.
        $i++;
        if ($i == 1) {
          // Line 1 should always be "num_candidates num_seats".
          $parts = explode(' ', $line);
          if (count($parts) != 2) {
            throw new DrooPHP_Exception('Line 1 must contain exactly two parts.');
          }
          $this->profile->setNumCandidates($parts[0]);
          $this->profile->setNumSeats($parts[1]);
          continue;
        }
        // If line 2 starts with a minus sign, it specifies the withdrawn candidate IDs.
        if ($i == 2 && strpos($line, '-') === 0) {
          $withdrawn = explode(' -', substr($line, 1));
          $withdrawn = array_map('intval', $withdrawn); // Candidate IDs are always integers.
          $this->profile->setWithdrawn($withdrawn);
        }
      }
    }
    catch (Exception $e) {
      throw new DrooPHP_Exception("Error in BLT data line $i: " . $e->getMessage());
    }
  }

  protected function _getDefaultOptions() {
    $options = array(
      'ron' => NULL,
    );
    return $options;
  }

}
