<?php
namespace RatingSync;

require_once "exceptions/HttpErrorException.php";
require_once "exceptions/HttpNotFoundException.php";
require_once "exceptions/HttpUnauthorizedRedirectException.php";

require_once "Constants.php";

/**
 *
 * Functions a child must implement
 *   - getNextRatingPageNumber
 */
abstract class Http
{
    protected $username;
    protected $sessionId;
    protected $baseUrl = null;
    protected $lightweightUrl = null;

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
    public function getPage($path, $postData = null, $headersOnly = false, $useBase = true)
    {
        if (! (!is_null($path)) ) {
            throw new \InvalidArgumentException("getPage() path cannot be NULL");
        }
        
        if ($useBase) {
            $path = $this->baseUrl.$path;
        }
        $ch = curl_init($path);
        $this->putCookiesInRequest($ch);
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
            throw new HttpUnauthorizedRedirectException('Unauthorized Redirect: ' . $path);
        } elseif (404 == $httpCode) {
            throw new HttpNotFoundException('HTTP Not Found: ' . $path);
        } elseif (($httpCode < 200) || (299 < $httpCode)) {
            throw new HttpErrorException('HTTP Error ' . $httpCode . ': ' . $path);
        }

        $headers = substr($result, 0, $info['header_size']);
        $this->storeCookiesFromResponse($headers);

        if ($headersOnly) {
            $page = $headers;
        } else {
            $page = substr($result, $info['header_size']);
        }

        return $page;
    }

    public function isPageValid($path) {
        $isValid = true;
        try {
            $this->getPage($path, null, true, false);
        } catch (\Exception $e) {
            $isValid = false;
        }

        return $isValid;
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
        // No-op. Made for a child class
    }

    /**
     * Set whatever cookies a website uses.  The \RatingSync\Http::getPage() calls
       this function after curl_exec.
     *
     * @param string $headers Http response
     *
     * @return none
     */
    protected function storeCookiesFromResponse($ch)
    {
        // No-op. Made for a child class
    }
}