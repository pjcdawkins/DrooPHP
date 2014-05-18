<?php
/**
 * @file
 * Load an election from a ballot (.blt) file.
 */

namespace DrooPHP\Source;

use DrooPHP\Ballot;
use DrooPHP\Candidate;
use DrooPHP\Election;
use DrooPHP\ElectionInterface;
use DrooPHP\Exception\BallotFileException;
use DrooPHP\Exception\InvalidBallotException;
use DrooPHP\Exception\ConfigException;

class File extends SourceBase {

  /**
   * The file resource handle.
   *
   * @var resource
   */
  public $file;

  /** @var int */
  protected $ballot_first_line;

  /** @var int */
  protected $num_candidates = 0;

  /** @var array */
  protected $withdrawn_cids = [];

  /**
   * @{inheritdoc}
   *
   * @throws ConfigException
   */
  public function loadElection() {
    $filename = $this->getConfig()->getOption('filename');
    // The filename is mandatory.
    if (!$filename) {
      throw new ConfigException('Filename not specified.');
    }
    // If the file is readable, convert the filename to an absolute path.
    if (!is_readable($filename) || !($realpath = realpath($filename))) {
      throw new ConfigException('File does not exist or cannot be read: ' . $filename);
    }
    $filename = $realpath;
    // Open the file.
    $this->file = fopen($filename, 'r');
    // Parse the file, populating an Election object.
    $election = new Election();
    $this->parse($election);
    // Close the file.
    fclose($this->file);
    return $election;
  }

  /**
   * Parse the BLT file to get election information.
   *
   * @see self::parseHead()
   * @see self::parseTail()
   * @see self::parseBallots()
   */
  protected function parse(ElectionInterface $election) {
    $this->parseHead($election);
    $this->parseTail($election);
    try {
      $this->parseBallots($election);
    } catch (InvalidBallotException $e) {
      $n = 10; // Number of characters to display for debugging.
      $position = ftell($this->file);
      fseek($this->file, -$n, SEEK_CUR);
      $snippet = fread($this->file, $n);
      throw new InvalidBallotException(
        sprintf(
          "Invalid ballot, position %d: %s. Previous %d characters: %s.",
          $position,
          rtrim($e->getMessage(), '.'),
          $n,
          str_replace(PHP_EOL, '\n', $snippet)
        )
      );
    }
  }

  /**
   * Read information from the beginning (head) of the BLT file.
   */
  protected function parseHead(ElectionInterface $election) {
    $line_number = 0;
    while (($line = fgets($this->file)) !== FALSE && $line_number <= 2) {
      // Remove comments (starting with # or // until the end of the line).
      $line = preg_replace('/(\x23|\/\/).*/', '', $line);
      // Trim whitespace.
      $line = trim($line);
      // Skip blank lines.
      if (!strlen($line)) {
        continue;
      }
      $line_number++;
      if ($line_number === 1) {
        // First line should always be "num_candidates num_seats".
        $parts = explode(' ', $line);
        if (count($parts) != 2) {
          throw new BallotFileException('The first line must contain exactly two parts.');
        }
        $this->num_candidates = (int) $parts[0];
        $election->setNumSeats((int) $parts[1]);
      }
      elseif ($line_number === 2) {
        if (strpos($line, '-') === 0) {
          // If line 2 starts with a minus sign, it specifies the
          // withdrawn candidate IDs.
          $this->withdrawn_cids = explode(' -', substr($line, 1));
          $this->ballot_first_line = 3;
        }
        else {
          $this->ballot_first_line = 2;
        }
      }
    }
  }

  /**
   * Read information from the end (tail) of the BLT file.
   */
  protected function parseTail(ElectionInterface $election) {
    // There can be a maximum of $this->num_candidates + 3 tail lines: each
    // candidate's name is given, and then there are optionally election,
    // title, and source.
    $lines_to_read = $this->num_candidates + 3;
    // Read the tail of the file.
    $pos = -1;
    $tail = [];
    // Keep seeking backwards through the file until the number of lines
    // left to read is 0.
    while ($lines_to_read > 0) {
      $char = NULL;
      // Keep seeking backwards through the line until finding a line feed
      // character.
      while ($char !== "\n" && fseek($this->file, $pos, SEEK_END) !== -1) {
        $char = fgetc($this->file);
        $pos--;
      }
      $line = fgets($this->file);
      // Remove comments (starting with # or // until the end of the
      // line).
      $line = preg_replace('/(\x23|\/\/).*/', '', $line);
      // Trim whitespace.
      $line = trim($line);
      // Skip blank lines.
      if (!strlen($line)) {
        continue;
      }
      // A line containing just 0 marks the end of the ballot lines, so
      // stop before reading anything before it (remember we're reading
      // backwards).
      if ($line === '0') {
        break;
      }
      $tail[] = $line;
      $lines_to_read--;
    }
    // Reverse so we can read forwards (optional lines are at the end).
    $tail = array_reverse($tail);
    // The minimum number of lines is the number of candidates.
    if (count($tail) < $this->num_candidates) {
      throw new BallotFileException('Candidate names not found');
    }
    foreach ($tail as $key => $line) {
      $info = trim($line, '"');
      if ($key < $this->num_candidates) {
        // This line is a candidate.
        $id = $key + 1;
        $candidate = new Candidate($info, $id);
        if (in_array($id, $this->withdrawn_cids)) {
          $candidate->setState(Candidate::STATE_WITHDRAWN);
        }
        $election->addCandidate($candidate);
      }
      elseif ($election->getTitle() === NULL) {
        $election->setTitle($info);
        break;
      }
    }
  }

  /**
   * Read the ballot lines of the BLT file, counting and validating.
   */
  protected function parseBallots(ElectionInterface $election) {
    $line_number = 0; // actually, this is the line number ignoring comments
    rewind($this->file);
    // Get config variables before looping (performance).
    $config = $this->getConfig();
    $allow_empty = $config->getOption('allow_empty');
    $allow_equal = $config->getOption('allow_equal');
    $allow_invalid = $config->getOption('allow_invalid');
    $allow_repeat = $config->getOption('allow_repeat');
    $allow_skipped = $config->getOption('allow_skipped');
    $candidates = $election->getCandidates();
    $ballots = [];
    $ballots_value = 0;
    while (($line = fgets($this->file)) !== FALSE) {
      // Remove comments (starting with # or // until the end of the line).
      if (strpos($line, '#') !== FALSE || strpos($line, '//') !== FALSE) {
        $line = preg_replace('/(\x23|\/\/).*/', '', $line);
      }
      // Trim whitespace.
      $line = trim($line);
      // Skip blank lines.
      if (!strlen($line)) {
        continue;
      }
      $line_number++;
      // Skip non-ballot lines.
      if ($line_number < $this->ballot_first_line) {
        continue;
      }
      // Stop at 0
      if ($line === '0') {
        break;
      }
      if (substr($line, -1) !== '0') {
        throw new BallotFileException("Ballot lines must end with 0.");
      }
      // Skip any ballot IDs at the beginning of the line.
      $close_bracket = strrpos($line, ')');
      if ($close_bracket) {
        $line = ltrim(substr($line, $close_bracket + 1));
      }
      // Remove the 0 character from the end of the line.
      $line = rtrim($line, ' 0');
      // Split the rest of the line into constituent parts, separated by spaces.
      $parts = explode(' ', $line);
      // The first part is always a ballot multiplier.
      $multiplier = (int) array_shift($parts);
      // All the other parts are the actual ranked candidates.
      // Save a $key for later use in sorting and identifying the ballot.
      $key = implode(' ', $parts);
      // Make sure that there aren't more rankings than the total number of candidates.
      if (count($parts) > $this->num_candidates) {
        throw new InvalidBallotException('The number of rankings exceeds the number of candidates.');
      }
      $no_equals = (strpos($line, '=') === FALSE);
      $ranking = [];
      $preference = 1;
      $valid = TRUE;
      // Loop through all the individual parts of the ballot,
      // validating them and adding them to $ranking.
      foreach ($parts as $part) {
        // Deal with skipped rankings: just move on.
        if ($part == '-') {
          if (!$allow_skipped) {
            $valid = FALSE;
            if (!$allow_invalid) {
              throw new InvalidBallotException('Skipped rankings are not allowed');
            }
            continue;
          }
          $part = NULL;
        }
        // If this is an 'equal ranking', split it into an array and
        // validate each side against known candidates.
        if (!$no_equals && strpos($part, '=') !== FALSE) {
          if (!$allow_equal) {
            $valid = FALSE;
            if (!$allow_invalid) {
              throw new InvalidBallotException('Equal rankings are not allowed');
            }
          }
          $part = explode('=', $part);
          foreach ($part as $cid) {
            if (!isset($candidates[$cid])) {
              $valid = FALSE;
              if (!$allow_invalid) {
                throw new InvalidBallotException("The candidate '$cid' does not exist.");
              }
            }
          }
        }
        // Deal with normal rankings.
        elseif ($part && !isset($candidates[$part])) {
          $valid = FALSE;
          if (!$allow_invalid) {
            throw new InvalidBallotException("The candidate '$part' does not exist.");
          }
        }
        // Check for repeat rankings.
        if ($part && !$allow_repeat && in_array($part, $ranking)) {
          $valid = FALSE;
          if (!$allow_invalid) {
            throw new InvalidBallotException('Repeat rankings are not allowed');
          }
          continue;
        }
        $ranking[$preference] = $part;
        $preference++;
      }
      if (empty($ranking) || $multiplier == 0) {
        $valid = FALSE;
        if (!$allow_empty && !$allow_invalid) {
          throw new InvalidBallotException('Empty ballots are not allowed');
        }
      }
      if (!$valid) {
        // The ballot is invalid: increment the total number of invalid ballots. // ERS97 5.1.2
        $election->addNumInvalidBallots($multiplier);
        continue;
      }
      // Register this ballot with its value.
      if (isset($ballots[$key])) {
        $ballots[$key]->addValue($multiplier);
      }
      else {
        $ballots[$key] = new Ballot($ranking, $multiplier);
      }
      $ballots_value += $multiplier;
    }
    ksort($ballots);
    $election->setBallots($ballots, $ballots_value);
  }

}
