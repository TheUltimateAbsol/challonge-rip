<?php
require_once 'TournamentRip.php';
require_once 'PlayersRip.php';
require_once 'NameRip.php';
require_once 'arrayExtract.php';

require_once 'Player.php';
require_once 'Match.php';

class Tournament
{
    // Tournament Specific Data;
    private $completed_at;
    private $id;
    private $name;
    private $participants_count;
    private $progress_meter;
    private $started_at;
    private $state;
    private $tournament_type;
    private $url; 
    private $host;
    private $full_challonge_url;
    
    // Touanment Aggregated Data
    private $matches;                   //Numbered array of match objects;
    private $participants;              //Numbered array of player objects;
    
    // constructor declaration
    function __construct($url){
        
        //Verify that it is a propery Challonge URL
        $url = $this->validateUrl($url);
        if ($url == false)
            throw new InvalidArgumentException("URL is not a valid Challonge URL!");
        
        $this->saveUrlData($url);
        
        $tRip = getTournamentData($url);
        $pRip = getPlayersData($url."/standings");
        
        //Save Tournament Data
        //This also makes sure that the tournament is of correct format
        $this->name = getName($url);
        $this->populateTouranmentData($tRip);
        
        //Create Player List
        $this->participants = $this->createParticipantList($pRip);
        
        //Create Matches
        $this->matches = $this->createMatchList($tRip);
        
        $this->aggregatePlayerData();
        $this->calculateTimes();
    }
    
    /**
     * Checks to make sure that the URL is valid from Challonge
     * Precondition: $url is validated
     * @param string $url a validated url
     * @return boolean|mixed the filtered url if valid, false otherwise
     */
    private function validateUrl(string $url){
        //Check if input string is a valid Challonge URL
        //If so, return the last part of it.
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if($url == false)
            return false;
        
        $components = parse_url($url);
        if($components == false)
            return false;
        
        //Make sure all necessary parts are there
        //print_r($components);
        if (!(array_key_exists("host", $components) &&
            array_key_exists("path", $components) &&
            array_key_exists("scheme", $components)))
            return false;
            
        $host = $components["host"];
        $path = $components["path"];
        $scheme = $components["scheme"];
        
        $pattern = "/challonge\.com$/i";
        
        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) &&
            preg_match($pattern, $host) === 1 &&
            $scheme == "http" &&
            substr_count($path, '/') == 1){
            return $url;
        }
        else 
            return false;
    }
    
    /**
     * Saves the data from the url
     * @param string $url
     */
    private function saveUrlData(string $url)
    {
        $components = parse_url($url);
        $host = $components["host"];
        $path = $components["path"];
        $scheme = $components["scheme"];
        
        //Full URl
        $this->full_challonge_url = $url;
        
        //Extract Host
        $pattern = "/([a-zA-Z]+)\.challonge\.com$/i";
        $result = preg_match($pattern, $host, $matches);
        if (sizeof($matches) > 0)
            $this->host = $matches[1];
        else
            $this->host = NULL;
        
        //Extract path
        $this->url = substr($path, 1);
    }
    /**
     * Updataes the Tournament-Object Specific Data using a Tournament Rip
     * @param array $tournamentRip
     * @throws InvalidDataException if tournament is not a complete double elimination bracket
     */
    private function populateTouranmentData(array $tournamentRip){
        
        //Validate Tournament
        $tournamentType = $tournamentRip["requested_plotter"];
        if ($tournamentType != "DoubleEliminationBracketPlotter")
            throw new InvalidDataException("tournamentType must be a DoubleEliminationBracketPlotter!");
        $tournamentData = $tournamentRip["tournament"];
        
        //Extract Data
        $this->id = arrayExtract($tournamentData, "id", "int");
        $this->progress_meter = arrayExtract($tournamentData, "progress_meter", "int");
        $this->state = arrayExtract($tournamentData, "state", "string");
        $this->tournament_type = arrayExtract($tournamentData, "tournament_type", "string");
                
        //Validate other variables
        if ($this->state != "complete")
            throw new InvalidDataException("The tournament is not finished yet!");
        
    }
    
    /**
     * Creates a participants list using data from the playerrip
     * Player id is not set
     * @param array $playerRip
     * @throws InvalidDataException if the playerRip contains non-array members
     */
    public function createParticipantList(array $playerRip)
    {
        $participants = array();
        foreach($playerRip as $p)
        {
            //Validate the list
            if(gettype($p) != "array")
                throw new InvalidDataException("playerRip should only contain arrays: ".gettype($p)." found");
            
            array_push($participants, new Player($p));
        }
        return $participants;
    }
    
    /**
     * Creates a match list using data from the tournamentRip
     * @param array $tournamentRip
     * @throws InvalidDataException if the tournamentRip does not have propery array inclusion
     * @return array
     */
    public function createMatchList(array $tournamentRip){
        $matchList = arrayExtract($tournamentRip, "matches_by_round", "array");
        $roundList = arrayExtract($tournamentRip, "rounds", "array");
        $matches = array();
        $matchNames = array();
        
        //Populate matchnames
        foreach ($roundList as $round)
        {
            //Validate the list
            if(gettype($round) != "array")
                throw new InvalidDataException("roundList should only contain arrays: ".gettype($round)." found");
                
            $matchNames[arrayExtract($round, "number", "int")] = arrayExtract($round, "title", "string");
        }
        
        //Populate matches
        foreach($matchList as $matchesByRound){
            //Validate the list
            if(gettype($matchesByRound) != "array")
                throw new InvalidDataException("matchList should only contain arrays: ".gettype($matchesByRound)." found");
            
            foreach($matchesByRound as $match)
            {
                //Validate the list
                if(gettype($match) != "array")
                    throw new InvalidDataException("matchList should only contain arrays: ".gettype($match)." found");
                
                $m = new Match($match);
                $m->setTitle($matchNames[$m->index("round")]); 
                array_push($matches, $m);
            }
        }
        
        return $matches;
    }
    
    /**
     * Finishes the data of the participant list using the matches list
     * Precondition: matches and participants are successfully created
     * 
     */
    public function aggregatePlayerData(){
        foreach($this->participants as $participant)
        {
            $participant->setTournamentId($this->id);
            $success = $this->completePlayerByMatches($participant);
            /*if($success == false)
                echo "NOOOOOOO";*/
        }
        $this->participants_count = sizeof($this->participants);
    }
    
    /**
     * Uses the data from matches to get the id and seed of the player
     * Precondition: matches is sucessfully created
     * @param Player $participant
     * @return boolean true if successful, false if the data is not found
     */
    public function completePlayerByMatches(Player &$participant){
        $name = $participant->index("name");
        
        foreach ($this->matches as $match)
        {
            $player1 = $match->index("player1");
            $player2 = $match->index("player2");
            
            $player1_name = arrayExtract($player1, "display_name", "string");
            $player2_name = arrayExtract($player2, "display_name", "string");
            
            if ($player1_name == $name)
            {
                $participant->setId(arrayExtract($player1, "id", "int"));
                $participant->setSeed(arrayExtract($player1, "seed", "int"));
                return true;
            }
            elseif ($player2_name == $name)
            {
                $participant->setId(arrayExtract($player2, "id", "int"));
                $participant->setSeed(arrayExtract($player2, "seed", "int"));
                return true;
            }
        }
        return false;
    }
    
    /**
     * Uses the underway_at variable of matches to determine start and end times
     * Precondition: Matches have been populated
     */
    private function calculateTimes(){
        //Get first round
        $firstIdentifier = 1;
        $lastIdentifier = sizeof($this->matches);
        foreach ($this->matches as $match)
        {
            $identifier = $match->index("identifier");
            if ($identifier == $firstIdentifier)
                $this->started_at = $match->index("underway_at");
            if ($identifier == $lastIdentifier)
                $this->completed_at = $match->index("underway_at");
        }
    }
    
    /**
     * Returns the value of a specific index, much like JSON
     * @param string $indexString The value which is indexed
     * @throws OutOfBoundsException if index is invalid
     */
    public function index($indexString) {
        switch ($indexString) {
            case "completed_at":
                return $this->completed_at;
                break;
            case "id":
                return $this->id;
                break;
            case "name":
                return $this->name;
                break;
            case "participants_count":
                return $this->participants_count;
                break;
            case "progress_meter":
                return $this->progress_meter;
                break;
            case "started_at":
                return $this->started_at;
                break;
            case "state":
                return $this->state;
                break;
            case "tournament_type":
                return $this->tournament_type;
                break;
            case "host":
                return $this->host;
                break;
            case "url":
                return $this->url;
                break;
            case "full_challonge_url":
                return $this->full_challonge_url;
                break;
            default: 
                throw new OutOfBoundsException($indexString." is not a valid index of Tournament!");
                break;
        }
    }

    public function toArray()
    {
        $matchArray = array();
        $participantArray = array();
        
        foreach ($this->matches as $match)
            array_push($matchArray, $match->toArray());
        
        foreach ($this->participants as $participant)
            array_push($participantArray, $participant->toArray());
        
        return array(
            "completed_at" => $this->completed_at,
            "id" => $this->id,
            "name" => $this->name,
            "participants_count" => $this->participants_count,
            "progress_meter" => $this->progress_meter,
            "started_at" => $this->started_at,
            "state" => $this->state,
            "tournament_type" => $this->tournament_type,
            "url" => $this->url,
            "host" => $this->host,
            "full_challonge_url" => $this->full_challonge_url,
            "matches" => $matchArray,
            "participants" => $participantArray
        );
    }
}
?>