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
   * The file resource handle.
   *
   * @var resource
   */
  public $file;

  /** @var DrooPHP_Election_Profile */
  public $profile;

  /** @var array */
  public $options = array();

  /**
   * The character position of the start of the first ballot line in the file.
   *
   * @var int
   */
  protected $_ballot_first_line_start;

  /**
   * The character position of the end of the last ballot line in the file.
   *
   * @var int
   */
  protected $_ballot_last_line_end;

  /**
   * Constructor: initiate a count by loading a BLT file.
   *
   * @todo allow passing in a complete profile instead of a file, perhaps.
   */
  public function __construct($filename, $options = array()) {
    $this->loadOptions($options);
    $this->file = fopen($filename, 'r');
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
   *
   * @todo work out how to 'tail' the file to get metadata lines instead of reading everything
   */
  public function parse() {
    $file = $this->file;
    $profile = $this->profile;
    try {
      $i = 0;
      while (($line = fgets($file)) !== FALSE) {
        // Remove comments (starting with # or // until the end of the line).
        $line = preg_replace('/(\x23|\/\/).*/', '', $line);
        // Skip blank lines.
        $untrimmed = $line;
        $line = trim($line);
        if (!strlen($line)) {
          continue;
        }
        $i++;
        if ($i === 1) {
          // First line should always be "num_candidates num_seats".
          $parts = explode(' ', $line);
          if (count($parts) != 2) {
            throw new DrooPHP_Exception('The first line must contain exactly two parts.');
          }
          $num_candidates = (int) $parts[0];
          $profile->setNumCandidates($num_candidates);
          $profile->setNumSeats($parts[1]);
          continue;
        }
        else if ($i === 2 && strpos($line, '-') === 0) {
          // If line 2 starts with a minus sign, it specifies the withdrawn candidate IDs.
          $withdrawn = explode(' -', substr($line, 1));
          $withdrawn = array_map('intval', $withdrawn); // Candidate IDs are always integers.
          $profile->setWithdrawn($withdrawn);
          continue;
        }
        else if ($line === '0') {
          // A line containing just 0 will be the one straight after the final ballot line.
          $this->_ballot_last_line_end = ftell($file) - strlen($untrimmed);
        }
        else if (strpos($line, '"') === 0) {
          // A line beginning with " is a metadata line (not a ballot line).
          if (!isset($metadata_first_line)) {
            $metadata_first_line = $i;
          }
          // The first n metadata lines are the candidate names, where n is the number of candidates.
          // The other metadata lines are the election title, then (if Wichmann-style) a source and then a comment line.
          if ($i - $metadata_first_line < $num_candidates) {
            $profile->addCandidate(trim($line, '"'));
          }
          else if ($profile->title === NULL) {
            $profile->title = trim($line, '"');
          }
          else if ($profile->source === NULL) {
            $profile->source = trim($line, '"');
          }
          else if ($profile->comment === NULL) {
            $profile->comment = trim($line, '"');
          }
          continue;
        }
        else if ($this->_ballot_first_line_start === NULL) {
          // All other lines are ballot lines. We only need to know about the first one for now.
          $this->_ballot_first_line_start = ftell($file) - strlen($untrimmed);
        }
      }
    }
    catch (Exception $e) {
      throw new DrooPHP_Exception('Error in BLT data line ' . $i . ': ' . $e->getMessage());
    }
  }

  protected function _getDefaultOptions() {
    $options = array(
      'ron' => FALSE,
    );
    return $options;
  }

}
