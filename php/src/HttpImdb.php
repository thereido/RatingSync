<?php
namespace RatingSync;

require_once "Http.php";

class HttpImdb extends Http
{
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
        if (!empty($this->sessionId)) {
            $cookies = "session-id=".$this->sessionId;

            if (!empty($this->cookieUu)) {
                $cookies = $cookies . ";uu=" . $this->cookieUu;
            }
            if (!empty($this->cookieCs)) {
                $cookies = $cookies . ";cs=" . $this->cookieCs;
            }
        }

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
        if (preg_match("@Set-Cookie: session-id=([^;]+);@i", $headers, $matches)) {
            $this->sessionId = $matches[1];
        }
        if (preg_match("@Set-Cookie: uu=([^;]+);@i", $headers, $matches)) {
            $this->cookieUu = $matches[1];
        }
        if (preg_match("@Set-Cookie: cs=([^;]+);@i", $headers, $matches)) {
            $this->cookieCs = $matches[1];
        }
    }
}