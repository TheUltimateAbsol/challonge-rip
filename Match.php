<?php
Class Match
{
    private $id;
    private $identifier;
    private $loser_id;
    private $player1_id;
    private $player2_id;
    private $round;
    private $title;
    private $state;
    private $tournament_id;
    private $underway_at;
    private $winner_id;
    private $player1;
    private $player2;
    private $player1_score;
    private $player2_score;
    private $is_score_valid;
    private $scores;
    
    /**
     * Creates a new match Object
     * @param array $matchArray An associative array from the tournamentRip
     */
    function __construct($matchArray){
        
        $this->round = arrayExtract($matchArray, "round", "int");
        $this->id = arrayExtract($matchArray, "id", "int");
        $this->identifier = arrayExtract($matchArray, "identifier", "int");
        $this->loser_id = arrayExtract($matchArray, "loser_id", "int");
        $this->state = arrayExtract($matchArray, "state", "string");
        $this->tournament_id = arrayExtract($matchArray, "tournament_id", "int");
        $this->underway_at = arrayExtract($matchArray, "underway_at", "string");
        $this->winner_id = arrayExtract($matchArray, "winner_id", "int");
        
        $this->player1 = arrayExtract($matchArray, "player1", "array");
        $this->player2 = arrayExtract($matchArray, "player2", "array");
        $this->player1_id = arrayExtract($this->player1, "id", "int");
        $this->player2_id = arrayExtract($this->player2, "id", "int");
        
        $this->scores = arrayExtract($matchArray, "scores", "array");
        
        //Empty Score brackets are possible
        if (sizeof($this->scores) == 2)
        {
            $this->player1_score = arrayExtract($this->scores, 0, "int");
            $this->player2_score = arrayExtract($this->scores, 1, "int");
        }
        else
        {
            $this->player1_score = 0;
            $this->player2_score = 0;
        }
        
        //Verify Scores are in desired range
        if(($this->player1_score >= 0 && $this->player1_score <=2) &&
           ($this->player2_score >= 0 && $this->player2_score <=2) &&
           ($this->player1_score > $this->player2_score || $this->player1_score < $this->player2_score))
            $this->is_score_valid = true;
       else
           $this->is_score_valid = false;
    }
    
    function setTitle(string $title){
        $this->title = $title;
    }
    
    /**
     * Returns the value of a specific index, much like JSON
     * @param string $indexString The value which is indexed
     * @throws OutOfBoundsException if index is invalid
     */
    public function index($indexString) {
        switch ($indexString) {
            case "id":
                return $this->id;
                break;
            case "identifier":
                return $this->identifier;
                break;
            case "title":
                return $this->title;
                break;
            case "loser_id":
                return $this->loser_id;
                break;
            case "player1":
                return $this->player1;
                break;
            case "player2":
                return $this->player2;
                break;
            case "player1_id":
                return $this->player1_id;
                break;
            case "player2_id":
                return $this->player2_id;
                break;
            case "round":
                return $this->round;
                break;
            case "title":
                return $this->title;
                break;
            case "state":
                return $this->state;
                break;
            case "tournament_id":
                return $this->tournament_id;
                break;
            case "underway_at":
                return $this->underway_at;
                break;
            case "winner_id":
                return $this->winner_id;
                break;
            case "scores":
                return $this->scores;
                break;
            case "player1_score":
                return $this->player1_score;
                break;
            case "player2_score":
                return $this->player2_score;
                break;
            case "is_score_valid":
                return$this->is_score_valid;
                break;
            default:
                throw new OutOfBoundsException($indexString." is not a valid index of Match!");
                break;
        }
    }
    
    public function toArray(){
        return array(
            "id" => $this->id,
            "identifier" => $this->identifier,
            "loser_id" => $this->loser_id,
            "player1_id" => $this->player1_id,
            "player2_id" => $this->player2_id,
            "round" => $this->round,
            "title" => $this->title,
            "state" => $this->state,
            "tournament_id" => $this->tournament_id,
            "underway_at" => $this->underway_at,
            "winner_id" => $this->winner_id,
            "player1" => $this->player1,
            "player2" => $this->player2,
            "player1_score" => $this->player1_score,
            "player2_score" => $this->player2_score,
            "is_score_valid" => $this->is_score_valid,
            "scores" => $this->scores
        );
    }
}