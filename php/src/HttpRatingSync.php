<?php
/**
 * HttpRatingSync class
 */
namespace RatingSync;

require_once "Http.php";

class HttpRatingSync extends \RatingSync\Http {
    public function __construct($username)
    {
        parent::__construct($username);
        $this->baseUrl = "/";
        $this->lightweightUrl = "index.php";
    }

    function _validateAfterConstructor() { return $this->validateAfterConstructor(); }

    function _getSessionId() { return $this->sessionId; }
    function _getLightweightUrl() { return $this->lightweightUrl; }
}

?>
