<?php
namespace RatingSync;

class HttpJinni
{
    protected $username;
    protected $jSessionID;
    protected $baseUrl = "http://www.jinni.com";

    public function __construct($username)
    {
        $this->username = $username;
    }

    public function getPage($path, $postData = null, $headersOnly = false)
    {
        $authCookie = "auth=".$this->username;
        $sessionCookie = "";
        if (!empty($this->jSessionID)) {
            $sessionCookie = ";JSESSIONID=".$this->jSessionID;
        }

        $ch = curl_init($baseUrl.$path);
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

        $headers = substr($result, 0, $info['header_size']);

        if (preg_match("@Set-Cookie: JSESSIONID=([^;]+);@i", $headers, $matches)) {
            $this->jSessionID = $matches[1];
        }

        if ($headersOnly) {
            $page = $headers;
        } else {
            $page = substr($result, $info['header_size']);
        }

        return $page;
    }

    public function searchSuggestions($searchStr, $type = null)
    {
        if (null !== $type && !Film::validContentType($type)) {
            throw new \Exception('Invalid content type: '.$type);
        }
        $return = $this->rawApiCall('AjaxController', 'findSuggestionsWithFilters', array($searchStr, (object)array('contentTypeFilter' => $type)));
        return $this->parseSearchSuggestionResults($return);
    }

    protected function rawApiCall($scriptName, $method, array $params = array())
    {
        if (!$this->jSessionID) {
            // Needs a session ID. Get a lightweight page
            $this->getPage('/sitemap.html');
        }
        $postData = 'callCount=1'."\n".
            'batchId=0'."\n".
            'httpSessionId='.$this->jSessionID."\n".
            'scriptSessionId=3C675DDBB02222BE8CB51E2415259E99676'."\n".
            'c0-scriptName='.$scriptName."\n".
            'c0-methodName='.$method."\n".
            'c0-id=0'."\n";

        $postData .= $this->buildApiParamString($params);

        return $this->getPage('/dwr/call/plaincall/AjaxUserRatingBean.dwr', $postData);
    }

    protected function buildApiParamString(array $params)
    {
        $paramStr = '';
        $i = 0;
        foreach ($params as $param) {
            $paramStr .= "c0-param$i=".$this->buildParamVar($param)."\n";
            $i++;
        }
        return $paramStr;
    }

    protected function buildParamVar($param)
    {
        if (is_int($param)) {
            return "number:$param";
        }
        if (is_object($param)) {
            $str = "Object_Object:{";
            foreach (get_object_vars($param) as $k => $x) {
                $str .= "$k:".$this->buildParamVar($x).',';
            }
            return rtrim($str, ',').'}';
        }
        if (is_null($param)) {
            return "null:null";
        }
        if (is_string($param)) {
            return "string:$param";
        }
    }

    protected function parseSearchSuggestionResults($str)
    {
        if (0 == preg_match("@dwr.engine._remoteHandleCallback\(\'\d+\',\'\d+\',\{results:([^,]+),@", $str, $matches)) {
            throw new \Exception('Could not parse API result');
        }

        preg_match_all("@s\d+.categoryType=null;s\d+.entityType='Title';s\d+.id=\"(\d+)\";s\d+.name=\"([^\"]+)\";s\d+.popularity=null;s\d+.titleType=\'([A-Z,a-z]+)';s\d+.year=(\d+);@", $str, $matches, PREG_SET_ORDER);

        $results = array();
        foreach ($matches as $match) {
            $results[] = array(
                'id' => $match[1],
                'name' => stripslashes($match[2]),
                'year' => $match[4],
                'contentType' => $match[3]
            );
        }
        return $results;
    }
}