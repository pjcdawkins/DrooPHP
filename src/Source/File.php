<?php
/**
 * @file
 * Load an election from a ballot (.blt) file.
 */

namespace DrooPHP\Source;

use DrooPHP\Ballot;
use DrooPHP\Election;
use DrooPHP\ElectionInterface;
use DrooPHP\Exception\InvalidBallotException;
use Stash;

class File extends SourceBase {

  /**
   * The file resource handle.
   *
   * @var resource
   */
  public $file;

  /** @var int */
  protected $ballot_first_line;

  /** @var Stash\Pool */
  protected $pool;

  /**
   * Overrides parent::getDefaults().
   *
   * Get the default options for loading a file.
   *
   * Possible options:
   *   filename       string  The path to a .blt file.
   *   allow_invalid  bool    Whether to continue loading despite encountering
   *                          invalid or spoiled ballots.
   *   allow_equal    bool    Whether to allow equal rankings (e.g. 2=3).
   *   allow_repeat   bool    Whether to allow repeat rankings (e.g. 3 2 2).
   *   allow_skipped  bool    Whether to allow skipped rankings (e.g. -).
   *   cache_enable   bool    Whether to cache the loaded ElectionInterface
   *                          object.
   *   cache_expire   int|\DateTime
   *                          A TTL (seconds) or DateTime expiry date.
   *   cache_driver   string|Stash\Driver\DriverInterface
   *                          The Stash cache driver.
   */
  public function getDefaults() {
    return parent::getDefaults() + [
      'cache_enable' => TRUE,
      'cache_expire' => 3600,
      'cache_driver' => extension_loaded('apc') ? 'Apc' : 'FileSystem',
      'cache_dir' => NULL,
    ];
  }

  /**
   * Get a Stash pool (caching).
   *
   * @throws \Exception
   * @return Stash\Pool
   */
  public function getStashPool() {
    if ($this->pool === NULL) {
      $driver_option = $this->getConfig()->getOption('cache_driver');
      if ($driver_option instanceof Stash\Driver\DriverInterface) {
        $driver = $driver_option;
      }
      elseif ($driver_option == 'FileSystem') {
        $options = [];
        // Allow cache_dir option to set the filesystem cache directory.
        $cache_dir = $this->getConfig()->getOption('cache_dir');
        if ($cache_dir) {
          if (!is_writable($cache_dir)) {
            throw new \Exception('The specified cache directory is not writable: ' . $cache_dir);
          }
          $options['path'] = $cache_dir;
        }
        $driver = new Stash\Driver\FileSystem($options);
      }
      elseif ($driver_option == 'Apc') {
        $driver = new Stash\Driver\Apc([
          'ttl' => $this->getConfig()->getOption('cache_expire'),
        ]);
      }
      else {
        throw new \Exception('Invalid value provided for option cache_driver.');
      }
      $this->pool = new Stash\Pool($driver);
    }
    return $this->pool;
  }

  /**
   * Get a cache key representing all the options affecting Election loading.
   */
  protected function getCacheKey($filename) {
    // Stash cache directories are based on the / separator in the key.
    return md5(dirname($filename)) . '/'
    . basename($filename) . '/'
    . serialize([
      'equal' => $this->getConfig()->getOption('allow_equal'),
      'skipped' => $this->getConfig()->getOption('allow_skipped'),
      'repeat' => $this->getConfig()->getOption('allow_repeat'),
      'invalid' => $this->getConfig()->getOption('allow_invalid'),
    ]);
  }

  /**
   * Overrides parent::loadElection().
   *
   * @throws \Exception
   * @return ElectionInterface
   */
  public function loadElection() {
    $filename = $this->getConfig()->getOption('filename');
    // The filename is mandatory.
    if (!$filename) {
      throw new \Exception('Filename not specified.');
    }
    // If the file is readable, convert the filename to an absolute path.
    if (!is_readable($filename) || !($realpath = realpath($filename))) {
      throw new \Exception('File does not exist or cannot be read: ' . $filename);
    }
    $filename = $realpath;
    // If caching is disabled, just load and return the Election.
    if (!$this->getConfig()->getOption('cache_enable')) {
      return $this->loadElectionWork($filename);
    }
    // Load the cache pool.
    $stash_pool = $this->getStashPool();
    $stash_item = $stash_pool->getItem($this->getCacheKey($filename));
    $election = $stash_item->get(Stash\Item::SP_OLD);
    // Invalidate the cache if the file changed since it was last loaded.
    $file_updated = (isset($election->file_last_loaded) && filemtime($filename) > $election->file_last_loaded);
    // Do the work again if the cache missed or should be refreshed.
    if ($stash_item->isMiss() || $file_updated) {
      $stash_item->lock();
      $election = $this->loadElectionWork($filename);
      // Save to cache.
      $stash_item->set($election, $this->getConfig()->getOption('cache_expire'));
    }
    return $election;
  }

  /**
   * Do the expensive and cacheable part of loading an Election.
   *
   * @param string $filename The absolute pathname to the ballot file.
   *
   * @return Election
   */
  public function loadElectionWork($filename) {
    // Parse the file, creating a new Election object.
    $election = new Election();
    $election->file_last_loaded = time();
    // Open the file.
    $this->file = fopen($filename, 'r');
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
  protected function parse($election) {
    try {
      $this->parseHead($election);
      $this->parseTail($election);
      $this->parseBallots($election);
    }
    catch (\Exception $e) {
      $n = 10; // Number of characters to display for debugging.
      $position = ftell($this->file);
      fseek($this->file, -$n, SEEK_CUR);
      $snippet = fread($this->file, $n);
      throw new \Exception(
        sprintf(
          "Error in BLT data, position %d: %s. Previous %d characters: %s.",
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
  protected function parseHead($election) {
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
          throw new \Exception('The first line must contain exactly two parts.');
        }
        $election->num_candidates = (int) $parts[0];
        $election->num_seats = (int) $parts[1];
      }
      else if ($line_number === 2) {
        if (strpos($line, '-') === 0) {
          // If line 2 starts with a minus sign, it specifies the
          // withdrawn candidate IDs.
          $withdrawn = explode(' -', substr($line, 1));
          // Candidate IDs are always integers.
          $withdrawn = array_map('intval', $withdrawn);
          $election->withdrawn = $withdrawn;
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
    $num_candidates = $election->num_candidates;
    // There can be a maximum of $num_candidates + 3 tail lines: each
    // candidate's name is given, and then there are optionally election,
    // title, and source.
    $lines_to_read = $num_candidates + 3;
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
    if (count($tail) < $num_candidates) {
      throw new \Exception('Candidate names not found');
    }
    foreach ($tail as $key => $line) {
      $info = trim($line, '"');
      if ($key < $num_candidates) {
        // This line is a candidate.
        $election->addCandidate($info);
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
   * Read the ballot lines of the BLT file, counting and validating.
   */
  protected function parseBallots($election) {
    $line_number = 0; // actually, this is the line number ignoring comments
    rewind($this->file);
    // Get config variables before looping (performance).
    $allow_equal = $this->getConfig()->getOption('allow_equal');
    $allow_invalid = $this->getConfig()->getOption('allow_invalid');
    $allow_repeat = $this->getConfig()->getOption('allow_repeat');
    $allow_skipped = $this->getConfig()->getOption('allow_skipped');
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
        throw new \Exception("Ballot lines must end with 0.");
      }
      // Skip any ballot IDs at the beginning of the line.
      $line = preg_replace('/^\([^\)]*\)\s?/', '', $line);
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
      if (count($parts) > $election->num_candidates) {
        throw new InvalidBallotException('The number of rankings exceeds the number of candidates.');
      }
      $no_equals = (strpos($line, '=') === FALSE);
      $ranking = [];
      $preference = 1;
      $valid = TRUE;
      try {
        // Loop through all the individual parts of the ballot,
        // validating them and adding them to $ranking.
        foreach ($parts as $part) {
          // Deal with skipped rankings: just move on.
          if ($part == '-') {
            if (!$allow_skipped) {
              throw new InvalidBallotException('Skipped rankings are not permitted in this count.');
              continue;
            }
            $part = NULL;
          }
          // If this is an 'equal ranking', split it into an array and
          // validate each side against known candidates.
          if (!$no_equals && strpos($part, '=') !== FALSE) {
            if (!$allow_equal) {
              throw new InvalidBallotException('Equal rankings are not permitted in this count.');
            }
            $part = explode('=', $part);
            foreach ($part as $cid) {
              if (!isset($election->candidates[$cid])) {
                throw new InvalidBallotException("The candidate '$cid' does not exist.");
              }
            }
          }
          // Deal with normal rankings.
          else if ($part && !isset($election->candidates[$part])) {
            throw new InvalidBallotException("The candidate '$part' does not exist.");
          }
          // Check for repeat rankings.
          if ($part && !$allow_repeat && in_array($part, $ranking)) {
            throw new InvalidBallotException('Repeat rankings are not allowed in this count.');
            continue;
          }
          $ranking[$preference] = $part;
          $preference++;
        }
        if (empty($ranking)) {
          throw new InvalidBallotException('Empty ballot.');
        }
      } catch (InvalidBallotException $e) {
        $valid = FALSE;
        if (!$allow_invalid) {
          throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
      }
      $election->num_ballots += $multiplier; // ERS97 5.1.1
      if (!$valid) {
        // The ballot is invalid: increment the total number of invalid ballots. // ERS97 5.1.2
        $election->num_invalid_ballots += $multiplier;
        continue;
      }
      // The ballot is valid: increment the total number of valid ballots. // ERS97 5.1.2
      $election->num_valid_ballots += $multiplier;
      if (isset($election->ballots[$key])) {
        // If an identical ballot already exists in the election, increase its value by $multiplier.
        $election->ballots[$key]->value += $multiplier;
      }
      else {
        // Otherwise, register this ballot with the initial value $multiplier.
        $election->ballots[$key] = new Ballot($ranking, $multiplier);
      }
    }
    // Sort the voting papers into first preferences // ERS97 5.1.2
    ksort($election->ballots);
  }

}
