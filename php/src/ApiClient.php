<?php
/**
 * ApiClient base class
 */
namespace RatingSync;

require_once "Constants.php";

abstract class ApiClient
{
    protected $baseUrl = null;
    protected $apiKey = null;

    abstract protected function jsonIndex($attrName, $requestName);

    /**
     * Return a cached api response if the cached file is fresh enough. The
     * $refreshCache param shows if it is fresh enough. If the file is out of
     * date return null.
     *
     * @param string         $filename          Cache file
     * @param int|0          $refreshCache      Use cache for files modified within mins from now. -1 means always use cache. Zero means never use cache.
     * @param boolean|true   $prependCachePath  Insert cache path to $filename if true, otherwise use the $filename as is
     *
     * @return string File as a string. Null if the use cache is not used.
     */
    public function readFromCache($filename, $refreshCache = Constants::USE_CACHE_ALWAYS, $prependCachePath = true)
    {
        if (Constants::USE_CACHE_NEVER == $refreshCache) {
            return null;
        }

        if ($prependCachePath) {
            $filename = Constants::cacheFilePath() . $filename;
        }
        
        if (!file_exists($filename) || (filesize($filename) == 0)) {
            return null;
        }

        $fileDateString = filemtime($filename);
        if (!$fileDateString) {
            return null;
        }

        $filestamp = date("U", $fileDateString);
        $refresh = true;
        if (Constants::USE_CACHE_ALWAYS == $refreshCache || ($filestamp >= (time() - ($refreshCache * 60)))) {
            $refresh = false;
        }
        
        if (!$refresh) {
            return file_get_contents($filename);
        } else {
            return null;
        }
    }

    /**
     * Cache a string as a local file
     *
     * @param string    $data File as a string
     * @param string    $filename Write the data to this filename
     * @param boolean|true   $prependCachePath  Insert cache path to $filename if true, otherwise use the $filename as is
     */
    public function writeToCache($data, $filename, $prependCachePath = true)
    {
        if (empty($filename)) {
            return;
        }

        if ($prependCachePath) {
            $filename = Constants::cacheFilePath() . $filename;
        }

        $fp = fopen($filename, "w");
        fwrite($fp, $data);
        fclose($fp);
    }

    /**
     * @return string|false API response as a string
     * @throws \RatingSync\UnauthorizedRedirectException
     * @throws \InvalidArgumentException
     */
    public function apiRequest($url, $postData = null, $headersOnly = false, $useBase = true)
    {
        if (! (!is_null($url)) ) {
            throw new \InvalidArgumentException(__CLASS__.__FUNCTION__." url cannot be NULL");
        }
        
        if ($useBase) {
            $url = $this->baseUrl.$url;
        }
        logDebug("url: $url", __CLASS__."::".__FUNCTION__.":".__LINE__);
        $ch = curl_init($url);
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
            throw new HttpUnauthorizedRedirectException('Unauthorized Redirect: ' . $url);
        } elseif (404 == $httpCode) {
            throw new HttpNotFoundException('HTTP Not Found: ' . $url);
        } elseif (($httpCode < 200) || (299 < $httpCode)) {
            throw new HttpErrorException('HTTP Error ' . $httpCode . ': ' . $url);
        }

        $headers = substr($result, 0, $info['header_size']);

        if ($headersOnly) {
            $response = $headers;
        } else {
            $response = substr($result, $info['header_size']);
        }
        
        return $response;
    }

    /**
     * Set whatever cookies an api uses.  The apiRequest() calls this function
     * before curl_exec.
     *
     * @param resource $ch This object is affect by this function
     *
     * @return none
     */
    protected function putCookiesInRequest($ch)
    {
        // No-op. Made for a child class
    }

    public function jsonValue($json, $attrName, $requestName)
    {
        if (!is_array($json)) {
            return null;
        }

        $value = null;
        $index = $this->jsonIndex($attrName, $requestName);
        if (!empty($index)) {
            $value = array_value_by_key($index, $json);
        }

        return $value;
    }

}

?>