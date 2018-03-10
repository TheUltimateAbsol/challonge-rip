<?php
require_once 'arrayExtract.php';

class Player
{
    private $final_rank;
    private $match_history;
    private $id; 
    private $name;
    private $seed; 
    private $tournament_id;
    
    /**
     * Creates a new player Object
     * Does not include id, seed, or tournament_id
     * @param array $playerArray An associative array from the playerRip
     */
    function __construct($playerArray){
        
        $this->name = arrayExtract($playerArray, "name", "string");
        $this->final_rank = arrayExtract($playerArray, "rank", "int");
        $this->match_history = arrayExtract($playerArray, "match_history", "array");
        
    }
    
    /**
     * Sets the id
     * @param int $id
     */
    function setId(int $id){
        $this->id = $id;
    }
    
    /**
     * Sets the seed
     * @param int $seed
     */
    function setSeed(int $seed){
        $this->seed = $seed;
    }
    
    /**
     * Sets the tournament_id
     * @param int $tournament
     */
    function setTournamentId(int $tournament){
        $this->tournament_id = $tournament;
    }
    
    
    /**
     * Returns the value of a specific index, much like JSON
     * @param string $indexString The value which is indexed
     * @throws OutOfBoundsException if index is invalid
     */
    public function index($indexString) {
        switch ($indexString) {
            case "final_rank":
                return $this->final_rank;
                break;
            case "id":
                return $this->id;
                break;
            case "name":
                return $this->name;
                break;
            case "seed":
                return $this->seed;
                break;
            case "tournament_id":
                return $this->tournament_id;
                break;
            case "match_history":
                return $this->match_history;
                break;
            default:
                throw new OutOfBoundsException($indexString." is not a valid index of Player!");
                break;
        }
    }
    
    
    public function toArray(){
        return array(
            "final_rank" => $this->final_rank,
            "match_history" => $this->match_history,
            "id" => $this->id,
            "name" => $this->name,
            "seed" => $this->seed,
            "tournament_id" => $this->tournament_id
        );
    }
    
}

