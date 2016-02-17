<?php
/**
 * HttpChild class for testing as a concrete Http child class
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Http.php";

class HttpChild extends \RatingSync\Http {
    public function __construct($username)
    {
        parent::__construct($username);
        $this->baseUrl = "http://www.imdb.com";
        $this->lightweightUrl = "/help/?general/&ref_=hlp_brws";
    }

    function _validateAfterConstructor() { return $this->validateAfterConstructor(); }

    function _getSessionId() { return $this->sessionId; }
    function _getLightweightUrl() { return $this->lightweightUrl; }

    public function searchSuggestions($searchStr, $type = null) {}
}

?>
