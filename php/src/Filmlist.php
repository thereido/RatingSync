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
    protected $parentListname;
    protected $listItems = array();  // Each item is a filmId

    public function __construct($username, $listname, $parentListname = NULL)
    {
        if (empty($username) || empty($listname)) {
            throw new \InvalidArgumentException("Filmlist must have a user and a name");
        }
        $this->username = $username;
        $this->listname = $listname;
        $this->parentListname = $parentListname;
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
     * @return string parent listName
     */
    public function getParentListname()
    {
        return $this->parentListname;
    }

    /**
     * @param string $parentListname
     *
     * @return none
     */
    public function setParentListname($parent)
    {
        $this->parentListname = $parent;
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
        $parentListname = $this->parentListname;

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

        // Validate that the parent list is a existing list
        $parentListColumn = "";
        $parentListValue = "";
        if (!empty($parentListname)) {
            $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname='$parentListname'";
            $result = $db->query($query);
            if ($result->num_rows == 1) {
                $parentListColumn = ", parent_listname";
                $parentListValue = ", '" . $parentListname . "'";
            }
        }

        // Replace (or insert) the filmlist
        $query = "REPLACE INTO user_filmlist (user_name, listname".$parentListColumn.")" .
                    " VALUES ('$username', '$listname'".$parentListValue.")";
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

    /**
     * Delete from the DB. If the list have children this fails.
     *
     * @return boolean True for success. False for error or there are children.
     */
    public function removeFromDb() {
        if (empty($this->username) || empty($this->listname)) {
            throw new \InvalidArgumentException(__FUNCTION__." username (".$this->username.") and listName (".$this->listname.") must not be empty");
        }
        $db = getDatabase();
        $success = true;

        // Check for children (Don't delete a list with children)
        $username = $this->username;
        $listname = $this->listname;
        $query = "SELECT * FROM user_filmlist" .
                    " WHERE parent_listname='$listname'" . 
                    "   AND user_name='$username'";
        $result = $db->query($query);
        if ($result->num_rows > 0) {
            $success = false;
        } else {
            // Delete all entries from this list
            $query = "DELETE FROM filmlist" .
                        " WHERE user_name='$username'" .
                        " AND listname='$listname'";
            if (! $db->query($query)) {
                $success = false;
                logDebug($query."\nSQL Error (".$db->errno.") ".$db->error, __FUNCTION__." ".__LINE__);
            }
        
            // Delete the list itself
            $query = "DELETE FROM user_filmlist WHERE user_name='$username' AND listname='$listname'";
            if (! $db->query($query)) {
                $success = false;
                logDebug($query."\nSQL Error (".$db->errno.") ".$db->error, __FUNCTION__." ".__LINE__);
            }
        }

        return $success;
    }

    /**
     * @return boolean True for success. False for error or there are children.
     */
    public static function removeListFromDb($username, $listname)
    {
        $list = new Filmlist($username, $listname);
        return $list->removeFromDb();
    }

    /**
     * @param array $contentFilter Filter out content in this array (do not return films that match)
     * @param array $listFilter Filter in by the user's other filmlists (do not return films unless they do match)
     */
    public function initFromDb($contentFilter = array(), $listFilter = array())
    {
        $username = $this->username;
        $listname = $this->listname;
        if (empty($username) || empty($listname)) {
            throw new \InvalidArgumentException(__FUNCTION__." \$username (".$username.") and \$listName (".$listname.") must not be empty");
        }

        $this->removeAllItems();
        $db = getDatabase();
        $contentFilteredOut = $this->getFilterCommaDelimited($contentFilter);

        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname='$listname'";
        $result = $db->query($query);
        if ($result->num_rows == 1) {
            $this->parentListname = $result->fetch_assoc()['parent_listname'];
        }

        $listFilterSubQuery = "";
        if (!empty($listFilter) && count($listFilter) > 0) {
            $listFilterSubQuery = "   AND film_id IN (SELECT film_id as id2 FROM filmlist as list2 WHERE user_name='$username' AND listname IN (";
            $comma = "";
            foreach ($listFilter as $filterListname) {
                if ($listname != $filterListname) {
                    $listFilterSubQuery .= $comma . "'".$filterListname."'";
                    $comma = ", ";
                }
            }
            $listFilterSubQuery .= "))";
        }

        if (empty($contentFilteredOut)) {
            $query  = "SELECT film_id FROM filmlist";
            $query .= " WHERE user_name='$username'";
            $query .= "   AND listname='$listname'";
            $query .=     $listFilterSubQuery;
            $query .= " ORDER BY position ASC";
        } else {
            $query  = "SELECT film_id FROM filmlist, film";
            $query .= " WHERE user_name='" .$this->username. "'";
            $query .= "   AND listname='" .$this->listname. "'";
            $query .=     $listFilterSubQuery;
            $query .= "   AND id=film_id";
            $query .= "   AND contentType NOT IN (" . $contentFilteredOut . ")";
            $query .= " ORDER BY position ASC";
        }
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $this->addItem($row['film_id']);
        }
    }

    public static function getListFromDb($username, $listname, $parentListname = null) {
        $list = new Filmlist($username, $listname, $parentListname);
        $list->initFromDb();

        return $list;
    }

    public static function getUserListsFromDb($username)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException(__FUNCTION__." \$username (".$username.") must not be empty");
        }

        $db = getDatabase();
        $lists = array();

        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' ORDER BY parent_listname ASC, listname ASC";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $listname = $row['listname'];
            $list = new Filmlist($username, $listname);
            $list->initFromDb();
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
        
        return $lists;
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

    /**
     * Returns a hierarchical array of listnames. The array has no items in the lists,
     * just names. By default it returns all lists for the user.
     *
     * @return array Keys are listname, parentListname, children. Children are a another array.
     */
    public static function getUserListsFromDbByParent($username, $populateFilms, $parentListname = null)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException(__FUNCTION__." \$username (".$username.") must not be empty");
        }

        $db = getDatabase();
        $listnames = array();  // Keys are listname, parentListname, children (arrays like this one)

        $parentSelect = "parent_listname IS NULL";
        if (!empty($parentListname)) {
            $parentSelect = "parent_listname='$parentListname'";
        }

        // From the DB build a flat array with listname the keys and parentList as the values
        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND $parentSelect ORDER BY listname ASC";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $listname = $row['listname'];
            $parentListname = $row['parent_listname'];
            $items = array();
            if ($populateFilms) {
                $filmlist = new Filmlist($username, $listname, $parentListname);
                $filmlist->initFromDb();
                $items = $filmlist->getItems();
            }
            $children = self::getUserListsFromDbByParent($username, $populateFilms, $listname);
            $listnames[] = array("listname" => $listname, "parentListname" => $parentListname, "items" => $items, "children" => $children);
        }

        return $listnames;
    }

    public function setFilmlist($filmId, $remove = false)
    {
        if ($remove) {
            $this->removeItem($filmId);
        } else {
            $this->addItem($filmId);
        }
    }

    public function getFilterCommaDelimited($filter) {
        $filteredOut = "";
        $comma = "";
        reset($filter);
        while (list($key, $val) = each($filter)) {
            if (Film::validContentType($key) && $val === false) {
                $filteredOut .= $comma . "'$key'";
                $comma = ", ";
            }
        }
        
        return $filteredOut;
    }

    public function getFilms($pageSize = null, $beginPage = 1)
    {
        $films = array();
        
        $currentItems = array();
        $chunks = array_chunk($this->listItems, $pageSize);
        $chunkIndex = $beginPage - 1;
        if (array_key_exists($chunkIndex, $chunks)) {
            $currentItems = $chunks[$chunkIndex];
        }
        
        $db = getDatabase();
        foreach ($currentItems as $filmId) {
            $film = Film::getFilmFromDb($filmId, $this->username);
            $films[] = $film;
        }
        
        return $films;
    }

    public static function getAncestorListnames($listname)
    {
        $db = getDatabase();
        $username = getUsername();
        $ancestors = array();

        while (!empty($listname)) {
            $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname='$listname'";
            $result = $db->query($query);
            if ($result->num_rows == 1) {
                $listname = $result->fetch_assoc()['parent_listname'];
                if (!empty($listname)) {
                    $ancestors[] = $listname;
                }
            }
        }

        return $ancestors;
    }
}