<?php
/**
 * Provider class
 */
namespace RatingSync;

require_once "Constants.php";

/**
 * Media streaming provider. Store and retrieve data for one stream
 * from one provider.
 */
class Stream
{
    protected $providerName;
    protected $filmId;
    protected $streamId;
    protected $url;
    protected $refresh;  // Date as a string Y-m-d (1999-01-01)
    protected $providerSite;

    public function __construct($providerName)
    {
        if (! self::validProvider($providerName) ) {
            throw new \InvalidArgumentException("Provider \$providerName ($providerName) invalid");
        }

        $this->providerName = $providerName;
    }

    public static function validProvider($providerName)
    {
        $providers = array(Constants::PROVIDER_NETFLIX,
                            Constants::PROVIDER_AMAZON,
                            Constants::PROVIDER_XFINITY,
                            Constants::PROVIDER_HULU,
                            Constants::PROVIDER_YOUTUBE,
                            Constants::PROVIDER_HBO);
        if (in_array($providerName, $providers))
        {
            return true;
        }
        return false;
    }

    public function getProviderName()
    {
        return $this->providerName;
    }

    public function setFilmId($filmId)
    {
        if (empty($filmId)) {
            $filmId = null;
        }
        $this->filmId = $filmId;
    }

    public function getFilmId()
    {
        return $this->filmId;
    }

    public function setStreamId($streamId)
    {
        if (empty($streamId)) {
            $streamId = null;
        }
        $this->streamId = $streamId;
    }

    public function getStreamId()
    {
        return $this->streamId;
    }

    public function setUrl($url)
    {
        if (empty($url)) {
            $url = null;
        }
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setRefresh()
    {
        $this->refresh = date("Y-m-d");
    }

    public function getProviderSite()
    {
        $site = $this->providerSite;
        if (empty($site)) {
            if ($this->getProviderName() == Constants::PROVIDER_NETFLIX) {
                $site = new Netflix("empty_username");
            }
            $this->site = $site;
        }

        return $site;
    }

    /**
     * Create a stream row in the db if not already exists
     */
    public function saveToDb()
    {
        if (!self::validProvider($this->getProviderName())) {
            throw new \InvalidArgumentException(__FUNCTION__." \$providerName (".$this->getProviderName().") invalid");
        } elseif (empty($this->getFilmId())) {
            throw new \InvalidArgumentException('filmId cannot be empty');
        }
        
        $refresh = $this->refresh;
        if (empty($refresh)) {
            return false;
        }
        
        // Look for an existing film row
        $db = getDatabase();
        $newRow = true;
        $dbRefresh = null;
        $providerName = $this->getProviderName();
        $filmId = $this->getFilmId();
        $result = $db->query("SELECT refresh FROM stream WHERE film_id=$filmId AND provider_name='$providerName'");
        if ($result->num_rows == 1) {
            $newRow = false;
            $dbRefresh = $result->fetch_assoc()['refresh'];
        }

        if (!empty($dbRefresh) && $dbRefresh > $refresh) {
            // Don't update when the one on the db is more up to date than this
            return false;
        }

        $filmId = $this->getFilmId();
        $streamId = $this->getStreamId();
        $url = $this->getUrl();
            
        $columns = "film_id, provider_name";
        $values = "$filmId, '$providerName'";
        $set = "SET";
        $setEmpty = $set;
        $setComma = "";
        if (!empty($streamId)) {
            $columns .= ", streamId";
            $values .= ", '$streamId'";
            $set .= "$setComma streamId='$streamId'";
            $setComma = ",";
        }
        if (!empty($url)) {
            $columns .= ", url";
            $values .= ", '$url'";
            $set .= "$setComma url='$url'";
            $setComma = ",";
        }
        if (!empty($refresh)) {
            $columns .= ", refresh";
            $values .= ", '$refresh'";
            $set .= "$setComma refresh='$refresh'";
            $setComma = ",";
        }

        if ($newRow) {
            $query = "INSERT INTO stream ($columns) VALUES ($values)";
            logDebug($query, __FUNCTION__." ".__LINE__);
            if (! $db->query($query)) {
                throw new \Exception('SQL Error ' . $db->errno . ". " . $db->error);
            }
        } else {
            if ($set != $setEmpty) {
                $query = "UPDATE stream $set WHERE film_id=$filmId AND provider_name='$providerName'";
                logDebug($query, __FUNCTION__." ".__LINE__);
                if (! $db->query($query)) {
                    throw new \Exception('SQL Error ' . $db->errno . ". " . $db->error);
                }
            }
        }

        return true;
    }

    public static function getStreamFromDb($providerName, $filmId)
    {
        if (!self::validProvider($providerName)) {
            throw new \InvalidArgumentException(__FUNCTION__." \$providerName ($providerName) invalid");
        } elseif (empty($filmId) || !is_int(intval($filmId))) {
            throw new \InvalidArgumentException(__FUNCTION__." \$filmId must be an int (filmId=$filmId)");
        }

        $db = getDatabase();
        $result = $db->query("SELECT * FROM stream WHERE film_id=$filmId AND provider_name=$providerName");
        if ($result->num_rows != 1) {
            throw new \Exception("Film not found by Film ID ($filmId) and Provider ($providerName)");
        }
        $row = $result->fetch_assoc();
        $stream = new Stream($providerName);
        $stream->setFilmId($filmId);
        $stream->setStreamId($row['streamId']);
        $stream->setUrl($row['url']);

        return $stream;
    }

    public function deleteFromDb()
    {
        if (!self::validProvider($this->getProviderName())) {
            throw new \InvalidArgumentException(__FUNCTION__." \$providerName (".$this->getProviderName().") invalid");
        } elseif (empty($this->getFilmId())) {
            throw new \InvalidArgumentException(__FUNCTION__." filmId cannot be empty");
        }
        
        $filmId = $this->getFilmId();
        $providerName = $this->getProviderName();
        $db = getDatabase();
        $query = "DELETE FROM stream WHERE film_id=$filmId AND provider_name='$providerName'";
        logDebug($query, __FUNCTION__." ".__LINE__);
        if (! $db->query($query)) {
            throw new \Exception('SQL Error ' . $db->errno . ". " . $db->error);
        }
    }

    /**
     * Refresh any streams for in the db for this is film if
     * they are older than 1 day.
     */
    public static function refreshStreamsByFilm($filmId)
    {
        if (empty($filmId) || !is_int(intval($filmId))) {
            throw new \InvalidArgumentException(__FUNCTION__." \$filmId must be an int (filmId=$filmId)");
        }

        $streamsNeedRefreshing = array(Constants::PROVIDER_NETFLIX);
        $streamsExisting = array();
        
        $db = getDatabase();
        $query = "SELECT * FROM stream WHERE film_id=$filmId";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $refresh = $row['refresh'];
            $providerName = $row['provider_name'];
            $streamsExisting[] = $providerName;
            $now = new \DateTime();
            $yesterday = $now->sub(new DateInterval('P1D'));
            if ($refresh > $yesterday && in_array($providerName, $streamsNeedRefreshing)) {
                $key = array_search($providerName, $streamsNeedRefreshing);
                unset($streamsNeedRefreshing[$key]);
            }
        }

        foreach ($streamsNeedRefreshing as $providerName) {
            $stream = new Stream($providerName);
            $stream->setFilmId($filmId);
            $stream->refresh(in_array($providerName, $streamsExisting));
        }
    }

    /**
     * Update a stream url from the provider's website.  It the stream
     * does not exist at the site, then remove it from the db.
     */
    public function refresh($site, $deleteIfMissing = true)
    {
        $db = getDatabase();
        $url = null;
        $site = $this->getProviderSite();
        if (!empty($site)) {
            $url = $site->getStreamingUrl($this->getFilmId());
        }
        if (empty($url)) {
            if ($deleteIfMissing) {
               $this->deleteFromDb();
            }
        } else {
            $this->setUrl($url);
            $this->setRefresh();
            $this->saveToDb();
        }
    }
}

?>
