<?php
namespace RatingSync;

require_once "Http.php";

class HttpJinni extends Http
{
    /**
     * (OBSOLOTE WEBSITE)
     *
     * Child class construct must set these members...
         $baseUrl
         $lightweightUrl
     *
     * @param string $username Account of the source website
     */
    public function __construct($username)
    {
        parent::__construct($username);
        $this->baseUrl = "http://www.jinni.com";
        $this->lightweightUrl = "";  // start page
    }

    public function getPage($path, $postData = null, $headersOnly = false, $useBase = true)
    {
        throw new \Exception('Obsolete website');
    }
}