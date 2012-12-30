<?php
namespace DrooPHP;

use DrooPHP\Exception\InvalidBallotException;
use DrooPHP\Method;

/**
 * Main class for a count, containing options and an election.
 */
class Count
{

    /**
     * The file resource handle.
     *
     * @var resource
     */
    public $file;

    /** @var array */
    public $options = array();

    /** @var DrooPHP\Election */
    public $election;

    /** @var int */
    protected $ballot_first_line;

    /**
     * Constructor: initiate a count by loading a BLT file.
     *
     * @todo allow passing in a complete election instead of a file, perhaps.
     */
    public function __construct($filename, $options = array())
    {
        $this->loadOptions($options);
        if (file_exists($filename) && is_readable($filename)) {
            $this->file = fopen($filename, 'r');
        }
        else {
            throw new Exception('File does not exist or cannot be read: ' . $filename);
        }
        $this->election = new Election;
        $this->parse();
        fclose($this->file);
    }

    /**
     * Set up options for this election.
     *
     * Possible options:
     *     allow_invalid => Whether to continue counting despite encountering an invalid/spoiled ballot.
     *     allow_equal => Whether to allow equal rankings (e.g. 2=3).
     *     allow_repeat => Whether to allow repeat rankings (e.g. 3 2 2).
     *     allow_skipped => Whether to allow skipped rankings (e.g. -).
     *     method => The name of a counting method class (must extend Method).
     *     maxStages => The maximum number of counting stages (to prevent infinite loops).
     *
     * @param array $options
     */
    public function loadOptions(array $options = array())
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $this->options = $options;
    }

    /**
     * Get the value of an option.
     *
     * @param string $option The name of the option.
     * @param mixed $or A value to return if the option doesn't exist.
     *
     * @return mixed
     */
    public function getOption($option, $or = NULL)
    {
        if ($or !== NULL && !isset($this->options[$option])) {
            return $or;
        }
        return $this->options[$option];
    }

    /**
     * Parse the BLT file to get election information.
     *
     * @see self::parseHead()
     * @see self::parseTail()
     * @see self::parseBallots()
     */
    public function parse()
    {
        try {
            $this->parseHead();
            $this->parseTail();
            $this->parseBallots();
        }
        catch (Exception $e) {
            $n = 10; // Number of characters to display for debugging.
            $position = ftell($this->file);
            fseek($this->file, -$n, SEEK_CUR);
            $snippet = fread($this->file, $n);
            throw new Exception(
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
    protected function parseHead()
    {
        $election = $this->election;
        $line_number = 0; // actually, this is the line number ignoring comments
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
                    throw new Exception('The first line must contain exactly two parts.');
                }
                $election->num_candidates = (int) $parts[0];
                $election->num_seats = (int) $parts[1];
            }
            else if ($line_number === 2) {
                if (strpos($line, '-') === 0) {
                    // If line 2 starts with a minus sign, it specifies the withdrawn candidate IDs.
                    $withdrawn = explode(' -', substr($line, 1));
                    $withdrawn = array_map('intval', $withdrawn); // Candidate IDs are always integers.
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
    protected function parseTail()
    {
        $election = $this->election;
        $num_candidates = $election->num_candidates;
        /*
         There can be a maximum of $num_candidates + 3 tail lines (each candidate
         is named and then there are optionally election, title, and source).
        */
        $lines_to_read = $num_candidates + 3;
        // Read the tail of the file.
        $pos = -1;
        $tail = array();
        // Keep seeking backwards through the file until the number of lines left to read is 0.
        while ($lines_to_read > 0) {
            $char = NULL;
            // Keep seeking backwards through the line until finding a line feed character.
            while ($char !== "\n" && fseek($this->file, $pos, SEEK_END) !== -1) {
                $char = fgetc($this->file);
                $pos--;
            }
            $line = fgets($this->file);
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
            throw new Exception('Candidate names not found');
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
     * Read the ballot lines of the BLT file, counting and validating.
     */
    protected function parseBallots()
    {
        $election = $this->election;
        $line_number = 0; // actually, this is the line number ignoring comments
        rewind($this->file);
        while (($line = fgets($this->file)) !== FALSE) {
            // Remove comments (starting with # or // until the end of the line).
            $line = preg_replace('/(\x23|\/\/).*/', '', $line);
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
                throw new Exception("Ballot lines must end with 0.");
            }
            // Skip the ballot IDs and 0 character.
            $line = preg_replace('/\(.*?\)\s?/', '', $line);
            $line = preg_replace('/\s0\b/', '', $line);
            // Split the line into constituent parts, separated by spaces.
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
            $ranking = array();
            $position = 1;
            $valid = TRUE;
            try {
                foreach ($parts as $part) {
                    if ($part == '-') {
                        if (!$this->options['allow_skipped']) {
                            throw new InvalidBallotException('Skipped rankings are not permitted in this count.');
                        }
                        continue;
                    }
                    if (strpos($part, '=')) {
                        if (!$this->options['allow_equal']) {
                            throw new InvalidBallotException('Equal rankings are not permitted in this count.');
                        }
                        $part = explode('=', $part);
                        foreach ($part as $cid) {
                            $this->validateCandidateId($cid); // throws InvalidBallotException
                        }
                    }
                    else {
                        $this->validateCandidateId($part); // throws InvalidBallotException
                    }
                    if (in_array($part, $ranking)) {
                        if (!$this->options['allow_repeat']) {
                            throw new InvalidBallotException('Repeat rankings are not allowed in this count.');
                        }
                        continue;
                    }
                    $ranking[$position] = $part;
                    $position++;
                }
                if (empty($ranking)) {
                    throw new InvalidBallotException('Empty ballot.');
                }
            }
            catch (InvalidBallotException $e) {
                $valid = FALSE;
                if (!$this->options['allow_invalid']) {
                    throw new Exception($e->getMessage(), $e->getCode(), $e);
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
        ksort($election->ballots); // @todo this is almost certainly unnecessary
    }

    /**
     * Check whether a candidate ID is valid.
     *
     * @param string $cid A candidate ID.
     *
     * @throws InvalidBallotException
     */
    protected function validateCandidateId($cid)
    {
        if (!isset($this->election->candidates[$cid])) {
            throw new InvalidBallotException("The candidate '$cid' does not exist.");
        }
    }

    /**
     * Get the default options for a count.
     */
    protected function getDefaultOptions()
    {
        return array(
            'allow_equal' => 0,
            'allow_skipped' => 0,
            'allow_repeat' => 0,
            'allow_invalid' => 1,
            'method' => 'Wikipedia',
            'maxStages' => 100,
        );
    }

}
