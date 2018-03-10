<?php
    include_once "Snoopy.class.php";

    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
    
    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Returns a complex associative array with a data dump from challonge
     * @param string $url The base url to the challonge tournament
     * @return array
     */
    function getTournamentData($url) {
        $snoopy = new Snoopy;
        
        $snoopy->user = "joe";
        $snoopy->pass = "bloe";
        
        if($snoopy->fetch($url))
        {            
            $html = $snoopy->results;
            $data = get_string_between($html, "['TournamentStore'] = ", "; window.");
            
            if ($data == "")
                echo "error retreiving tournament data. Perhaps Challonge updated the formatting?";
            else
            {
               if (!isJson($data))
                   echo "error parsing JSON Data. Maybe Challonge ruined the script?";
               $data = json_decode($data, true);
               /*echo "<pre>";
               print_r($data);
               echo "</pre>";*/
               return $data;
            }
                
        }
        else
            echo "error fetching document: ".$snoopy->error."\n";
    }
 ?>
