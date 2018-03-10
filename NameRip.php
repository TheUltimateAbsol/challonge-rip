<?php

/**
 * Returns the name of the tournament at the requested url, or an empty string if not found.
 * @param string $url The base url to the challonge tournament
 * @return string
 */
function getName(string $url) {
    $playersdata = array();
    
    //Load the HTML from the site
    $html = file_get_contents($url);
    $dom_document = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom_document->loadHTML($html);
    
    
    //use DOMXpath to navigate the html with the DOM
    $dom_xpath = new DOMXpath($dom_document);
    
    // if you want to get the div with id=interestingbox
    $elements = $dom_xpath->query("//*[@id = 'title']");
    
    if (!is_null($elements) && $elements->length > 0) {
        foreach ($elements as $element) {
            
            $nodes = $element->childNodes;
            foreach ($nodes as $node) {
                return trim($node->nodeValue);
            }
        }
    }
    else 
        return "";
}
?>