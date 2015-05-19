<?php
namespace RatingSync;

require_once "exceptions/HttpErrorException.php";
require_once "exceptions/HttpNotFoundException.php";
require_once "exceptions/HttpUnauthorizedRedirectException.php";

abstract class Http
{
    protected $username;
    protected $jSessionId;
    protected $baseUrl = null;
    protected $lightweightUrl = null;
    
    /**
     * Suggestions in a search text input while a user types
     *
     * @param string $searchStr The current text in the text input
     * @param string $type      \RatingSync\Film content type
     *
     * @return array results ['id', 'title', 'year', 'contentType']
     */
    abstract public function searchSuggestions($searchStr, $type = null);

    /**
     * Child class construct must set these members...
         $baseUrl
         $lightweightUrl
     *
     * @param string $username Account of the source website
     */
    public function __construct($username)
    {
        if (! (is_string($username) && 0 < strlen($username)) ) {
            throw new \InvalidArgumentException('$username must be non-empty');
        }
        $this->username = $username;
    }

    /**
     * Validate that the child constructor is initiated
     *
     * @return bool true for valid, false otherwise
     */
    protected function validateAfterConstructor()
    {
        if (empty($this->baseUrl) || empty($this->lightweightUrl)) {
            return false;
        }
        return true;
    }

    /**
     * @return string|false HTML as string or false if the page is not found
     * @throws \RatingSync\UnauthorizedRedirectException
     * @throws \InvalidArgumentException
     */
    public function getPage($path, $postData = null, $headersOnly = false)
    {
        if (! (!is_null($path)) ) {
            throw new \InvalidArgumentException("getPage() path cannot be NULL");
        }

        $authCookie = "auth=".$this->username;
        $sessionCookie = "";
        if (!empty($this->jSessionId)) {
            $sessionCookie = ";JSESSIONID=".$this->jSessionId;
        }

        $ch = curl_init($this->baseUrl.$path);
        curl_setopt($ch, CURLOPT_COOKIE, $authCookie . $sessionCookie);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");

        if (is_array($postData)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        } elseif (is_string($postData)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        // Verify HTTP Code
        $httpCode = $info['http_code'];
        if (401 == $httpCode) {
            throw new HttpUnauthorizedRedirectException('Unauthorized Redirect: ' . $this->baseUrl . $path);
        } elseif (404 == $httpCode) {
            throw new HttpNotFoundException('HTTP Not Found: ' . $this->baseUrl . $path);
        } elseif (($httpCode < 200) || (299 < $httpCode)) {
            throw new HttpErrorException('HTTP Error ' . $httpCode . ': ' . $this->baseUrl . $path);
        }

        $headers = substr($result, 0, $info['header_size']);

        if (preg_match("@Set-Cookie: JSESSIONID=([^;]+);@i", $headers, $matches)) {
            $this->jSessionId = $matches[1];
        }

        if ($headersOnly) {
            $page = $headers;
        } else {
            $page = substr($result, $info['header_size']);
        }
        
/* FIXME
        if (strpos($page, '<form id=\'unauthorizedRedirectForm\'')) {
            throw new UnauthorizedRedirectException('Unauthorized Redirect: ' . $this->baseUrl . $path);
        } elseif (strpos($page, '<h1 class="title1">Whoops!</h1>')) {
            throw new \Exception('Whoops!: ' . $this->baseUrl . $path);
        }
*/

        return $page;
    }

    /**
     * If there is no session id, go to a lightweight page to set it
     */
    protected function setJSessionId()
    {
        if (!$this->jSessionId) {
            $this->getPage($this->lightweightUrl);
        }
    }
}