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
   *   equal => Whether or not to allow equal rankings (e.g. 2=3).
   *   method => The name of a counting method class (must extend DrooPHP_Method).
   *
   * @param array $options
   */
  public function loadOptions(array $options) {
    $options = array_merge($this->_getDefaultOptions(), $options);

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
   * Read the ballot lines, getting the number of votes per candidate at
   * $preference_level, for a given candidate (chosen at the previous preference
   * level).
   *
   * @param int $preference_level
   *   The preference level for which to count votes.
   * @param mixed $from_cid
   *   The ID of the candidate from whom votes will be transferred.
   *
   * @return array
   *   An array of votes, keyed by candidate ID.
   */
  public function getVoteRatio($preference_level, $from_cid = NULL) {
    $file = $this->file;
    rewind($file);
    $election = $this->election;
    $num_candidates = $election->getNumCandidates();
    // Array of votes keyed by candidate ID.
    $votes = array();
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
      $line = preg_replace('/\s0\b/', '', $line);
      $parts = explode(' ', $line);
      // The first part is always a ballot multiplier.
      $multiplier = (int) array_shift($parts);
      // If there isn't an item at this preference level, don't do anything.
      if (!isset($parts[$preference_level - 1])) {
        continue;
      }
      // Count only those where $from_cid is the ID of the previous level candidate.
      if ($from_cid === NULL) {
        throw new DrooPHP_Exception('Votes cannot be counted: no previous-level candidate specified.');
      }
      if ($parts[$preference_level - 2] != $from_cid) {
        continue;
      }
      $item = $parts[$preference_level - 1];
      // A - signifies a skipped ranking.
      if ($item == '-') {
        continue;
      }
      if (strpos($item, '=')) {
        // If the item contains a = sign, it is an equal ranking (e.g. 1=2).
        if (!$this->options['equal']) {
          throw new DrooPHP_Exception('Equal rankings are not permitted in this count.');
        }
        $equated = array();
        foreach (explode('=', $item) as $cid) {
          if (in_array($cid, $equated)) {
            throw new DrooPHP_Exception('Candidates cannot be ranked equal with themselves.');
          }
          $equated[] = $cid;
          if (!isset($votes[$cid])) {
            $votes[$cid] = 0;
          }
          $votes[$cid] += $multiplier;
        }
      }
      else {
        // Otherwise, the item is a candidate ID.
        $cid = $item;
        if (!isset($votes[$cid])) {
          $votes[$cid] = 0;
        }
        $votes[$cid] += $multiplier;
      }
    }
    return $votes;
  }

  /**
   * Read the ballot lines of the BLT file, counting and validating ballot lines.
   *
   * @return void
   */
  protected function _parseBallots() {
    $file = $this->file;
    rewind($file);
    $election = $this->election;
    $num_candidates = $election->getNumCandidates();
    $num_ballots = 0;
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
      $line = preg_replace('/\s0\b/', '', $line);
      // Stop on finding equal rankings if they are not permitted.
      if (!$this->options['equal'] && strpos($line, '=') !== FALSE) {
        throw new DrooPHP_Exception('Equal rankings are not permitted in this count.');
      }
      // Split the line into constituent parts, separated by spaces.
      $parts = explode(' ', $line);
      // The first part is always a ballot multiplier. All other parts are rankings.
      $multiplier = (int) array_shift($parts);
      $num_ballots += $multiplier;
      // Make sure that it doesn't contain more rankings than the total number of candidates.
      if (count($parts) > $num_candidates) {
        throw new DrooPHP_Exception('Too many preferences.');
      }
      // Count the first-preference votes.
      $item = $parts[0];
      // A - signifies a skipped ranking.
      if ($item == '-') {
        continue;
      }
      if (strpos($item, '=')) {
        // If the item contains a = sign, it is an equal ranking (e.g. 1=2).
        $equated = array();
        foreach (explode('=', $item) as $cid) {
          if (in_array($cid, $equated)) {
            throw new DrooPHP_Exception('Candidates cannot be ranked equal with themselves.');
          }
          $equated[] = $cid;
          $candidate = $election->getCandidate($cid);
          $candidate->addVotes($multiplier);
        }
      }
      else {
        // Otherwise, the item is a candidate ID.
        $cid = $item;
        $candidate = $election->getCandidate($cid);
        $candidate->addVotes($multiplier);
      }
    }
    $election->setNumBallots($num_ballots);
  }

  protected function _getDefaultOptions() {
    $options = array(
      'equal' => TRUE,
      'method' => 'DrooPHP_Method',
    );
    return $options;
  }

}
