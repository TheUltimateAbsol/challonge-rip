<?php

/**
 * Returns a an array of players with small details about their ranking, if available
 * @param string $url The base url to the challonge tournament
 * @return array
 */
function getPlayersData($url) {
    $playersdata = array();
    
    //Load the HTML from the site
    $html = file_get_contents($url);
    $dom_document = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom_document->loadHTML($html);
    
    
    //use DOMXpath to navigate the html with the DOM
    $dom_xpath = new DOMXpath($dom_document);
    
    // if you want to get the div with id=interestingbox
    $elements = $dom_xpath->query("//tbody");
    
    if (!is_null($elements) && ($elements->length > 0)) 
    {
        foreach ($elements as $element) {
            $players = $element->childNodes;
            
            $currRank = 1;
            //iterate over each player
            for ($i = 0; $i < $players->length; $i++)
            {    
                $player = $players->item($i)->childNodes;
                //iterate over each player element
                
                //Find player variables depending on how many elements are in the table
                if ($player->length > 6)
                {
                    $rank = intval($player->item(0)->nodeValue);
                    $currRank = $rank;
                    $name = $player->item(2)->nodeValue;
                    $history = $player->item(6)->nodeValue;
                }
                else
                {
                    $rank = $currRank;
                    $name = $player->item(0)->nodeValue;
                    $history = $player->item(4)->nodeValue;
                }
                   
                $name = trim($name);
                $history = preg_replace('/\W+/', '', $history);
                
                $playerdata = array(
                    "name" => $name,
                    "rank" => $rank,
                    "match_history" => str_split($history)
                );
                array_push($playersdata, $playerdata);
            }
        }
        /*echo "<pre>";
        print_r($playersdata);
        echo "</pre>";*/
        return $playersdata;
    }
    else 
        echo "No results found :(";
}
?>