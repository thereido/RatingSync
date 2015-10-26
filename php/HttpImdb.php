<?php
namespace RatingSync;

require_once "Http.php";

class HttpImdb extends Http
{
    protected $cookieUu;
    protected $cookieCs;
    protected $cookieId;

    /**
     * Child class construct must set these members...
         $baseUrl
         $lightweightUrl
     *
     * @param string $username Account of the source website
     */
    public function __construct($username)
    {
        parent::__construct($username);
        $this->baseUrl = "http://www.imdb.com";
        $this->lightweightUrl = "/help/?general/&ref_=hlp_brws";
    }

    public function searchSuggestions($searchStr, $type = null) {}

    /**
     * Set whatever cookies a website uses.  The \RatingSync\Http::getPage() calls
       this function before curl_exec.
     *
     * @param resource $ch This object is affect by this function
     *
     * @return none
     */
    protected function putCookiesInRequest($ch)
    {
        $cookies = "";
echo "<pre>Putting cookies:";
        if (!empty($this->sessionId)) {
            $cookies = "session-id=".$this->sessionId;
echo "\nsession-id: ".$this->sessionId;
        }
        if (!empty($this->cookieUu)) {
            $cookies = $cookies . ";uu=" . $this->cookieUu;
echo "\nuu: " . $this->cookieUu;
        }
        if (!empty($this->cookieCs)) {
            $cookies = $cookies . ";cs=" . $this->cookieCs;
echo "\ncs: " . $this->cookieCs;
        }
        if (!empty($this->cookieId)) {
            $cookies = ";id=".$this->cookieId;
echo "\nid: " . $this->cookieId;
        }
echo "</pre>";

        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    }

    /**
     * Set whatever cookies a website uses.  The \RatingSync\Http::getPage() calls
       this function after curl_exec.
     *
     * @param string $headers Http response
     *
     * @return none
     */
    protected function storeCookiesFromResponse($headers)
    {
echo "<pre>Headers:\n$headers</pre>";
        if (preg_match("@Set-Cookie: session-id=([^;]+);@i", $headers, $matches)) {
            $this->sessionId = $matches[1];
        }
        if (preg_match("@Set-Cookie: uu=([^;]+);@i", $headers, $matches)) {
            $this->cookieUu = $matches[1];
        }
        if (preg_match("@Set-Cookie: cs=([^;]+);@i", $headers, $matches)) {
            $this->cookieCs = $matches[1];
        }
        if (preg_match("@Set-Cookie: id=([^;]+);@i", $headers, $matches)) {
            $this->cookieId = $matches[1];
        }
$this->cookieId = "BCYte1g54hpE-GHXE68TSM4TzCg9vomJVHDVD_jz1tnwgw-ie0Dsjqw9Tq5EMIwRTsnqA3vam_f1iuH8ld80w0khWqfjemNTj-IYh9sqgO3q3Xva5s5QPwY_wfKPCtpspoQfijHO-bEjxBGK44K7vUrUbTGUsNkM1qdafSY5FR-XC_Q";
echo "<pre>Storing... \nsessionId: " . $this->sessionId . "\nid: " . $this->cookieId . "</pre>";
    }
}