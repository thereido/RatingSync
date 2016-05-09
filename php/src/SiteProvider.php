<?php
namespace RatingSync;

require_once "Site.php";

abstract class SiteProvider extends \RatingSync\Site
{
    protected $streamUrlIsDetailPage = true;

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
            try {
                $film = $this->getFilmBySearchByFilm($film);
            } catch (\Exception $e) {
                $url = null;
            }
        }

        if (!empty($film) && !empty($film->getUniqueName($this->sourceName))) {
            $url = $this->getStreamUrlByFilm($film, $onlyFree);
        }
        
        return $url;
    }

    public function getStreamUrlByFilm($film, $onlyFree = true)
    {
        if (is_null($film) || !($film instanceof Film)) {
            throw new \InvalidArgumentException(__FUNCTION__." \$film must be a \RatingSync\Film object");
        } elseif (empty($film->getUniqueName($this->sourceName))) {
            throw new \InvalidArgumentException(__FUNCTION__." \$film must be have an uniqueName set");
        }

        $url = null;
        $page = null;
        try {
            $page = $this->getFilmDetailPage($film, 60); // use cache within 60 minutes
        } catch (\Exception $e) {
            $url = null;
        }

        if (!empty($page)) {
            $url = $this->getStreamUrlByPage($page, $film, $onlyFree);
        }
        
        return $url;
    }

    public function getStreamUrlByPage($page, $film, $onlyFree = true)
    {
        $url = null;
        if ($this->streamAvailableFromDetailPage($page, $film, $onlyFree)) {
            if ($this->streamUrlIsDetailPage) {
                $url = $this->getFilmUrl($film);
            } else {
                $regex = $this->getDetailPageRegexForStreamingUrl();
                if (!empty($regex) && 0 != preg_match($regex, $page, $matches)) {
                    $url = htmlspecialchars_decode($matches[1]);
                }
            }
        }

        return $url;
    }

    /**
     * Returns true as long the page is not empty. On some sites the film
     * only appears if stream is available (eg. Netflix). Most child sites
     * need to overwrite this function to check more info about the detail
     * page.
     *
     * @param string $page HTML film detail page
     *
     * @return boolean true if available for streaming otherwise false
     */
    protected function streamAvailableFromDetailPage($page, $film, $onlyFree = true)
    {
        return (!empty($page));
    }

    /**
     * Regular expression to find Streaming URL in film detail HTML page. Not
     * all sites use a regex to find the steam URL. Many use the detail page
     * itself as the stream URL. Return is null else a child class changes it.
     *
     * @return string Regular expression to find Streaming URL in film detail HTML page
     */
    protected function getDetailPageRegexForStreamingUrl()
    {
        return null;
    }
}

?>