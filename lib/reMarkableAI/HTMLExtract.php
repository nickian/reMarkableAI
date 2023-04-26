<?php
/**
 * DOM functions wrapper to help parse email and prompt resonse content.
 *
 * @package reMarkableAI/HTMLExtract
 * @version 1.0
 * @license MIT
 */

namespace reMarkableAI;

class HTMLExtract {

    /**
     * Extract content inside of HTML <p> tags in a string.
     *
     * @param string $html The HTML to look for tags in
     * 
     * @return array $pTagsContent List of content from all p tags
     */
    public function extractPTagsContent($html) {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
    
        $pTagsContent = [];
        $pTags = $xpath->query('//p');
        foreach ($pTags as $pTag) {
            $pTagsContent[] = $pTag->nodeValue;
        }
        return $pTagsContent;
    }


    /**
     * Extract content inside of HTML <h1> tags in a string.
     *
     * @param string $html The HTML to look for tags in
     * 
     * @return array $h1TagsContent List of content from all h1 tags
     */
    public function extractH1TagsContent($html) {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $h1TagsContent = [];
        $h1Tags = $xpath->query('//h1');
        return $h1Tags[0]->nodeValue;
    }

}