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
   * Constructor: initiate a count by loading a BLT file.
   *
   * @todo allow passing in a complete profile instead of a file, perhaps.
   */
  public function __construct($filename, $options = array()) {
    $this->loadOptions($options);
    if (file_exists($filename) && is_readable($filename)) {
      $this->file = fopen($filename, 'r');
    }
    else {
      throw new DrooPHP_Exception('File does not exist or cannot be read: ' . $filename);
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
   */
  public function parse() {
    try {
      $this->_parseHead();
      $this->_parseTail();
      return TRUE;
    }
    catch (Exception $e) {
      throw new DrooPHP_Exception(
        'Error in BLT data, position ' . ftell($this->file) . ': ' . $e->getMessage()
      );
      return FALSE;
    }
  }

  /**
   * Read information from the beginning (head) of the BLT file.
   *
   * @return void
   */
  protected function _parseHead() {
    $file = $this->file;
    $profile = $this->profile;
    $i = 0;
    while (($line = fgets($file)) !== FALSE && $i <= 2) {
      // Remove comments (starting with # or // until the end of the line).
      $line = preg_replace('/(\x23|\/\/).*/', '', $line);
      // Trim whitespace.
      $line = trim($line);
      // Skip blank lines.
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
        $profile->setNumCandidates($parts[0]);
        $profile->setNumSeats($parts[1]);
      }
      else if ($i === 2 && strpos($line, '-') === 0) {
        // If line 2 starts with a minus sign, it specifies the withdrawn candidate IDs.
        $withdrawn = explode(' -', substr($line, 1));
        $withdrawn = array_map('intval', $withdrawn); // Candidate IDs are always integers.
        $profile->setWithdrawn($withdrawn);
      }
    }
  }

  /**
   * Read information from the end (tail) of the BLT file.
   *
   * @return void
   */
  protected function _parseTail() {
    $file = $this->file;
    $profile = $this->profile;
    $num_candidates = $profile->getNumCandidates();
    /*
     There can be a maximum of $num_candidates + 3 tail lines (each candidate
     is named and then there are optionally election, title, and source).
    */
    $lines_to_read = $num_candidates + 3;
    // Read the tail of the file.
    $pos = -2;
    $tail = array();
    // Keep seeking backwards through the file until the number of lines left to read is 0.
    while ($lines_to_read > 0) {
      $char = NULL;
      // Keep seeking backwards through the line until finding a line feed character.
      while ($char !== "\n" && fseek($file, $pos, SEEK_END) !== -1) {
        $char = fgetc($file);
        $pos--;
      }
      $line = fgets($file);
      // Remove comments (starting with # or // until the end of the line).
      $line = preg_replace('/(\x23|\/\/).*/', '', $line);
      // Trim whitespace.
      $line = trim($line);
      // Skip blank lines.
      if (!strlen($line)) {
        continue;
      }
      /*
       A line containing just 0 marks the end of the ballot lines, so stop
       before reading anything before it (remember we're reading backwards).
      */
      if ($line === '0') {
        break;
      }
      $tail[] = $line;
      $lines_to_read--;
    }
    // Reverse so we can read forwards (because optional lines are at the end).
    $tail = array_reverse($tail);
    // The minimum number of lines is the number of candidates.
    if (count($tail) < $num_candidates) {
      throw new DrooPHP_Exception('Candidate names not found');
    }
    foreach ($tail as $key => $line) {
      $info = trim($line, '"');
      if ($key < $num_candidates) {
        // This line is a candidate.
        $profile->addCandidate($info);
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
    }
  }

  protected function _getDefaultOptions() {
    $options = array(
      'ron' => FALSE,
    );
    return $options;
  }

}
