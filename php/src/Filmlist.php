<?php
/**
 * Filmlist class
 *
 * @category 
 * @package  RatingSync
 * @license
 * @author   thereido <github@bagowine.com>
 * @link     https://github.com/thereido/RatingSync
 */
namespace RatingSync;

require_once "Constants.php";

/**
 * List of films. The list is owned my a user.
 *
 * Source - Local, IMDb, RottenTomatoes, Jinni, etc.
 *
 * @category 
 * @package  RatingSync
 * @license
 * @author   thereido <github@bagowine.com>
 * @link     https://github.com/thereido/RatingSync
 */
class Filmlist
{    
    protected $listname;
    protected $username;
    protected $listItems = array();  // Each item is a filmId

    public function __construct($username, $listname)
    {
        if (empty($username) || empty($listname)) {
            throw new \InvalidArgumentException("Filmlist must have a user and a name");
        }
        $this->username = $username;
        $this->listname = $listname;
    }

    /**
     * @return string listName
     */
    public function getListname()
    {
        return $this->listname;
    }

    /**
     * @param string $name
     *
     * @return none
     */
    public function setListname($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException("Filmlist must have a name");
        }

        $this->listname = $name;
    }

    /**
     * @return string username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $name
     *
     * @return none
     */
    public function setUsername($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException(__FUNCTION__." Username must have a name");
        }

        $this->username = $name;
    }

    public function addItem($filmId)
    {
        if (!is_numeric($filmId)) {
            throw new \InvalidArgumentException(__FUNCTION__.' param must be a number');
        }

        if (!in_array(intval($filmId), $this->listItems)) {
            $this->listItems[] = intval($filmId);
        }
    }

    public function removeItem($removeThisFilmId)
    {
        if (!in_array($removeThisFilmId, $this->listItems)) {
            return;
        }
        
        $remainingItems = array();
        foreach ($this->listItems as $filmId) {
            if ($removeThisFilmId != $filmId) {
                $remainingItems[] = $filmId;
            }
        }
        $this->listItems = $remainingItems;
    }

    public function removeAllItems()
    {
        $this->listItems = array();
    }

    public function getItems()
    {
        return $this->listItems;
    }

    public function inList($item)
    {
        return in_array($item, $this->listItems);
    }

    public function count() {
        return count($this->listItems);
    }

    public function saveToDb()
    {
        if (empty($this->username) || empty($this->listname)) {
            throw new \InvalidArgumentException(__FUNCTION__." username (".$this->username.") and listName (".$this->listname.") must not be empty");
        }

        $db = getDatabase();
        $errorFree = true;
        $username = $this->username;
        $listname = $this->listname;

        $removeFilmIds = "";
        $comma = "";
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $filmId = $row['film_id'];
            if (!in_array($filmId, $this->listItems)) {
                // Array for removing items
                $removeFilmIds = $filmId;
                $comma = ", ";
            }
        }

        // Remove items
        if (!empty($removeFilmIds)) {
            $query = "DELETE FROM filmlist" .
                     " WHERE user_name='$username'" .
                       " AND listname='$listname'" .
                       " AND film_id IN ($removeFilmIds)";
            if (! $db->query($query)) {
                logDebug($query."\nSQL Error (".$db->errno.") ".$db->error, __FUNCTION__." ".__LINE__);
                $errorFree = false;
            }
        }

        // Replace (or insert) the filmlist
        $query = "REPLACE INTO user_filmlist (user_name, listname)" .
                    " VALUES ('$username', '$listname')";
        if (! $db->query($query)) {
            logDebug($query."\nSQL Error (".$db->errno.") ".$db->error, __FUNCTION__." ".__LINE__);
            $errorFree = false;
        }

        // Replace (or insert) items
        $position = 1;
        foreach ($this->listItems as $filmId) {
            $query = "REPLACE INTO filmlist (user_name, listname, film_id, position)" .
                     " VALUES ('$username', '$listname', $filmId, $position)";
            if (! $db->query($query)) {
                logDebug($query."\nSQL Error (".$db->errno.") ".$db->error, __FUNCTION__." ".__LINE__);
                $errorFree = false;
            }
            $position++;
        }

        return $errorFree;
    }

    public function removeFromDb() {
        if (empty($this->username) || empty($this->listname)) {
            throw new \InvalidArgumentException(__FUNCTION__." username (".$this->username.") and listName (".$this->listname.") must not be empty");
        }
        $db = getDatabase();
        
        $username = $this->username;
        $listname = $this->listname;
        $query = "DELETE FROM filmlist" .
                    " WHERE user_name='$username'" .
                    " AND listname='$listname'";
        if (! $db->query($query)) {
            logDebug($query."\nSQL Error (".$db->errno.") ".$db->error, __FUNCTION__." ".__LINE__);
        }
    }

    public static function removeListFromDb($username, $listname) {
        $list = new Filmlist($username, $listname);
        $list->removeFromDb();
    }

    public function initFromDb()
    {
        $username = $this->username;
        $listname = $this->listname;
        if (empty($username) || empty($listname)) {
            throw new \InvalidArgumentException(__FUNCTION__." \$username (".$username.") and \$listName (".$listname.") must not be empty");
        }
        $this->removeAllItems();

        $db = getDatabase();
        
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $this->addItem($row['film_id']);
        }
    }

    public static function getListFromDb($username, $listname) {
        $list = new Filmlist($username, $listname);
        $list->initFromDb();

        return $list;
    }

    public static function getUserListsFromDb($username, $asJson = false)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException(__FUNCTION__." \$username (".$username.") must not be empty");
        }

        $db = getDatabase();
        $lists = array();

        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' ORDER BY listname ASC";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $listname = $row['listname'];
            $list = new Filmlist($username, $listname);
            $lists[$listname] = $list;
        }

        $query = "SELECT * FROM filmlist WHERE user_name='$username' ORDER BY listname ASC, position ASC";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $list = null;
            $listname = $row['listname'];
            $filmId = $row['film_id'];
            $lists[$listname]->addItem($filmId);
        }
        
        if ($asJson) {
            $arr = array();
            foreach ($lists as $list) {
                $arrList = array();
                $arrList['listname'] = $list->getListname();
                $arrList['username'] = $list->getUsername();
                $arrItems = array();
                foreach ($list->getItems() as $item) {
                    $arrItems[] = $item;
                }
                $arrList['items'] = $arrItems;
                $arr[] = $arrList;
            }
            return json_encode($arr);
        } else {
            return $lists;
        }
    }

    public static function saveToDbUserFilmlistsByFilmObjectLists($username, $film)
    {
        if (empty($username) || empty($film) || empty($film->getId())) {
            throw new \InvalidArgumentException(__FUNCTION__." \$username ($username) and \$film with an ID must not be empty");
        }
        
        $filmId = $film->getId();
        $dbLists = self::getUserListsFromDb($username);
        $dbListnames = array();
        $objectListnames = $film->getFilmlists();

        foreach ($dbLists as $dbList) {
            // Remove an item that the object does not have this listname
            if (!in_array($dbList->getListname(), $objectListnames)) {
                $dbList->removeItem($filmId);
                $dbList->saveToDb();
            }

            $dbListnames[] = $dbList->getListname();
        }

        // Add items from the object that the dbLists do not have
        foreach ($objectListnames as $name) {
            if (!in_array($name, $dbListnames)) {
                $newList = new Filmlist($username, $name);
                $newList->addItem($filmId);
                $newList->saveToDb();
            }
        }
    }

    public function setFilmlist($filmId, $remove = false)
    {
        if ($remove) {
            $this->removeItem($filmId);
        } else {
            $this->addItem($filmId);
        }
    }
}