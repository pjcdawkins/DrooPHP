<?php
/**
 * @file
 *   DrooPHP_Count class.
 * @package
 *   DrooPHP
 */

/**
 * @class
 *   DrooPHP_Count
 *   Main class for a count, containing options and an election.
 */
class DrooPHP_Count {

  /**
   * The file resource handle.
   *
   * @var resource
   */
  public $file;

  /** @var DrooPHP_Election */
  public $election;

  /** @var array */
  public $options = array();

  /**
   * Constructor: initiate a count by loading a BLT file.
   *
   * @todo allow passing in a complete election instead of a file, perhaps.
   */
  public function __construct($filename, $options = array()) {
    $this->loadOptions($options);
    if (file_exists($filename) && is_readable($filename)) {
      $this->file = fopen($filename, 'r');
    }
    else {
      throw new DrooPHP_Exception('File does not exist or cannot be read: ' . $filename);
    }
    $this->election = new DrooPHP_Election;
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
   * Parse the BLT file to create the election election.
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
    $election = $this->election;
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
        // First line should always be "_num_candidates num_seats".
        $parts = explode(' ', $line);
        if (count($parts) != 2) {
          throw new DrooPHP_Exception('The first line must contain exactly two parts.');
        }
        $election->setNumCandidates($parts[0]);
        $election->setNumSeats($parts[1]);
      }
      else if ($i === 2 && strpos($line, '-') === 0) {
        // If line 2 starts with a minus sign, it specifies the withdrawn candidate IDs.
        $withdrawn = explode(' -', substr($line, 1));
        $withdrawn = array_map('intval', $withdrawn); // Candidate IDs are always integers.
        $election->setWithdrawn($withdrawn);
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
    $election = $this->election;
    $_num_candidates = $election->getNumCandidates();
    /*
     There can be a maximum of $_num_candidates + 3 tail lines (each candidate
     is named and then there are optionally election, title, and source).
    */
    $lines_to_read = $_num_candidates + 3;
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
    if (count($tail) < $_num_candidates) {
      throw new DrooPHP_Exception('Candidate names not found');
    }
    foreach ($tail as $key => $line) {
      $info = trim($line, '"');
      if ($key < $_num_candidates) {
        // This line is a candidate.
        $election->addCandidate($info); // @todo support non-integer candidate IDs
      }
      else if ($election->title === NULL) {
        $election->title = $info;
      }
      else if ($election->source === NULL) {
        $election->source = $info;
      }
      else if ($election->comment === NULL) {
        $election->comment = $info;
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
