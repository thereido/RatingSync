<?php
namespace RatingSync;

require_once "exceptions/HttpErrorException.php";
require_once "exceptions/HttpNotFoundException.php";
require_once "exceptions/HttpUnauthorizedRedirectException.php";

require_once "Constants.php";

class Http
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
    public function __construct($sourceName, $username = null)
    {
        $this->username = $username;
        $this->baseUrl = self::getBaseUrl($sourceName);
        $this->lightweightUrl = self::getLightweightUrl($sourceName);

        if (empty($this->baseUrl) || empty($this->lightweightUrl)) {
            throw new \InvalidArgumentException("Http constructor of \$sourceName ($sourceName) invalid");
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
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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

    public static function getBaseUrl($sourceName)
    {
        $baseUrl = "";

        if ($sourceName == Constants::SOURCE_RATINGSYNC) {
            $baseUrl = Constants::RS_HOST;
        } elseif ($sourceName == Constants::SOURCE_IMDB) {
            $baseUrl = "http://www.imdb.com";
        } elseif ($sourceName == Constants::SOURCE_JINNI) {
            $baseUrl = "http://www.jinni.com";
        } elseif ($sourceName == Constants::SOURCE_NETFLIX) {
            $baseUrl = "";
        } elseif ($sourceName == Constants::SOURCE_AMAZON) {
            $baseUrl = "";
        } elseif ($sourceName == Constants::SOURCE_XFINITY) {
            $baseUrl = "https://tv.xfinity.com";
        }

        return $baseUrl;
    }

    public static function getLightweightUrl($sourceName)
    {
        $lightweightUrl = "";

        if ($sourceName == Constants::SOURCE_RATINGSYNC) {
            $lightweightUrl = "/index.php";
        } elseif ($sourceName == Constants::SOURCE_IMDB) {
            $lightweightUrl = "/help/?general/&ref_=hlp_brws";
        } elseif ($sourceName == Constants::SOURCE_JINNI) {
            $lightweightUrl = "/about";
        } elseif ($sourceName == Constants::SOURCE_NETFLIX) {
            $lightweightUrl = "/Login?locale=en-US";
        } elseif ($sourceName == Constants::SOURCE_AMAZON) {
            $lightweightUrl = "/random?content_type=1+2&prime=2";
        } elseif ($sourceName == Constants::SOURCE_XFINITY) {
            $lightweightUrl = "/mytv/dvr?CMPID=xtvg_footer";
        }

        return $lightweightUrl;
    }
}