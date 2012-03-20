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

  /** @var int */
  protected $_ballot_first_line;

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
   * Parse the BLT file to get election information.
   *
   * @see self::_parseHead()
   * @see self::_parseTail()
   * @see self::_parseBallots()
   *
   * @return void
   */
  public function parse() {
    try {
      $this->_parseHead();
      $this->_parseTail();
      $this->_parseBallots();
    }
    catch (Exception $e) {
      throw new DrooPHP_Exception(
        'Error in BLT data, position ' . ftell($this->file) . ': ' . $e->getMessage()
      );
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
      else if ($i === 2) {
        if (strpos($line, '-') === 0) {
          // If line 2 starts with a minus sign, it specifies the withdrawn candidate IDs.
          $withdrawn = explode(' -', substr($line, 1));
          $withdrawn = array_map('intval', $withdrawn); // Candidate IDs are always integers.
          $election->setWithdrawn($withdrawn);
          $this->_ballot_first_line = 3;
        }
        else {
          $this->_ballot_first_line = 2;
        }
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
    $num_candidates = $election->getNumCandidates();
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

  /**
   * Read the ballot lines of the BLT file, setting up the candidates' initial
   * votes for each round.
   *
   * @return void
   */
  protected function _parseBallots() {
    $file = $this->file;
    rewind($file);
    $election = $this->election;
    $num_candidates = $election->getNumCandidates();
    $i = 0;
    while (($line = fgets($file)) !== FALSE) {
      // Remove comments (starting with # or // until the end of the line).
      $line = preg_replace('/(\x23|\/\/).*/', '', $line);
      // Trim whitespace.
      $line = trim($line);
      // Skip blank lines.
      if (!strlen($line)) {
        continue;
      }
      $i++;
      // Skip non-ballot lines.
      if ($i < $this->_ballot_first_line) {
        continue;
      }
      // Stop at 0.
      if ($line === '0') {
        break;
      }
      if (substr($line, -1) !== '0') {
        throw new DrooPHP_Exception('Ballot lines must end with 0.');
      }
      // Skip the ballot IDs and 0 character.
      $line = preg_replace('/\(.*?\)\s?/', '', $line);
      $line = preg_replace('/\s0$/m', '', $line);
      $parts = explode(' ', $line);
      // The first part is always a ballot multiplier.
      $multiplier = (int) array_shift($parts);
      foreach ($parts as $key => $cid) {
        // Exclude skipped rankings.
        if ($cid == '-') {
          continue;
        }
        $preference = $key + 1;
        if ($preference > $num_candidates) {
          throw new DrooPHP_Exception('Too many preferences.');
        }
        // If the item contains a = sign, it is an equal ranking (e.g. 1=2).
        if (strpos($cid, '=')) {
          if (!$this->options['equal']) {
            throw new DrooPHP_Exception('Equal rankings are not permitted in this count.');
          }
          $added_equal = array();
          foreach (explode('=', $cid) as $cid_equal) {
            if (in_array($cid_equal, $added_equal)) {
              throw new DrooPHP_Exception('Candidates cannot be ranked equal with themselves.');
            }
            $candidate = $election->getCandidate($cid_equal);
            $candidate->addVotes($preference, $multiplier);
            $added_equal[] = $cid_equal;
          }
          continue;
        }
        $candidate = $election->getCandidate($cid);
        $candidate->addVotes($preference, $multiplier);
      }
    }
  }

  protected function _getDefaultOptions() {
    $options = array(
      'ron' => FALSE,
      'equal' => TRUE,
    );
    return $options;
  }

}
