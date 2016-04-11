<?php
namespace RatingSync;

require_once "exceptions/HttpErrorException.php";
require_once "exceptions/HttpNotFoundException.php";
require_once "exceptions/HttpUnauthorizedRedirectException.php";

require_once "Constants.php";

class Http
{
    const SITE_SOURCE   = "Source";
    const SITE_PROVIDER = "Provider";

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
    public function __construct($siteType, $siteName, $username = null)
    {
        $this->username = $username;

        if ($siteType == self::SITE_SOURCE) {

            if ($siteName == Constants::SOURCE_RATINGSYNC) {
                $this->baseUrl = Constants::RS_HOST;
                $this->lightweightUrl = "/index.php";
            } elseif ($siteName == Constants::SOURCE_IMDB) {
                $this->baseUrl = "http://www.imdb.com";
                $this->lightweightUrl = "/help/?general/&ref_=hlp_brws";
            } elseif ($siteName == Constants::SOURCE_JINNI) {
                $this->baseUrl = "http://www.jinni.com";
                $this->lightweightUrl = "/about";
            }
            
        } elseif ($siteType == self::SITE_PROVIDER) {

            if ($siteName == Constants::PROVIDER_NETFLIX) {
                $this->baseUrl = "http://instantwatcher.com";
                $this->lightweightUrl = "/about";
            }
            
        } else {
            throw new \InvalidArgumentException("Http constructor \$siteType ($siteType) must be ".self::SITE_SOURCE." or ".self::SITE_PROVIDER);
        }

        if (empty($this->baseUrl) || empty($this->lightweightUrl)) {
            throw new \InvalidArgumentException("Http constructor of type $siteType with \$siteName ($siteName) invalid");
        }
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
            $this->getPage($path, null, true, true);
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

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}