<?php
namespace RatingSync;

require_once "Site.php";

abstract class SiteProvider extends \RatingSync\Site
{

    /**
     * Regular expression to find Streaming URL in film detail HTML page
     *
     * @return string Regular expression to find Streaming URL in film detail HTML page
     */
    abstract protected function getDetailPageRegexForStreamingUrl();

    /**
     * Return getFilmDetailPageUrl($filmId) if it is available for streaming.
     * The return includes base URL.
     */
    public function getStreamUrl($filmId, $onlyFree = true)
    {
        if (empty($filmId) || !is_int(intval($filmId))) {
            throw new \InvalidArgumentException(__FUNCTION__." \$filmId must be an int (filmId=$filmId)");
        }

        $url = null;
        
        $film = Film::getFilmFromDb($filmId);
        if (empty($film->getUniqueName($this->sourceName))) {
            $searchTerms = array();
            $searchTerms["title"] = $film->getTitle();
            $searchTerms["year"] = $film->getYear();
            $film = $this->getFilmBySearch($searchTerms);
        }
        $page = null;
        try {
            $page = $this->getFilmDetailPage($film, 60); // use cache within 60 minutes
        } catch (\Exception $e) {
            $url = null;
        }
        if (!empty($page)) {
            $regex = $this->getDetailPageRegexForStreamingUrl();
            if (0 != preg_match($regex, $page, $matches)) {
                $url = $matches[1];
            }
        }
        
        return $url;
    }
}

?>