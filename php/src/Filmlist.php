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

use ArrayObject;

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
    protected $sort;
    protected $sortDirection;
    protected $contentFilter = array();
    protected $listFilter = array();
    protected $genreFilter = array();

    // Genre filter with Any or All
    //   True matches a film with Any genre in the filter
    //   False matches a film with All genres in the filter
    protected $genreFilterMatchAny = true;

    public function __construct($username, $listname, $parentListname = NULL)
    {
        if (empty($username) || empty($listname)) {
            throw new \InvalidArgumentException("Filmlist must have a user and a name");
        }
        $this->username = $username;
        $this->listname = $listname;
        $this->parentListname = $parentListname;
        $this->sort = ListSortField::position;
        $this->sortDirection = SqlSortDirection::descending;
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

    public function setSort(ListSortField $sort): void
    {
        if (! $sort->validated() ) {
            throw new \InvalidArgumentException(__FUNCTION__." Invalid sort param '$sort->value'");
        }

        $this->sort = $sort;
    }

    public function getSort(): ListSortField
    {
        return $this->sort;
    }

    public function getOrderColumn()
    {
        $column = "";

        if ($this->getSort() == ListSortField::position) {
            $column = "filmlist.next_film_id";
        } elseif ($this->getSort() == ListSortField::modified) {
            $column = "filmlist.create_ts";
        }

        return $column;
    }

    public function setSortDirection( SqlSortDirection $sortDirection ): void
    {
        if (! $sortDirection->validated() ) {
            throw new \InvalidArgumentException(__FUNCTION__." Invalid sortDirection param '$sortDirection->value'");
        }

        $this->sortDirection = $sortDirection;
    }

    public function getSortDirection(): SqlSortDirection
    {
        return $this->sortDirection;
    }

    /**
     * @return array Content Type filter
     */
    protected function getContentFilter()
    {
        return $this->contentFilter;
    }

    /**
     * @param array $contentFilter
     *
     * @return none
     */
    public function setContentFilter($contentFilter)
    {
        if (!is_array($contentFilter) && !is_null($contentFilter)) {
            throw new \InvalidArgumentException(__FUNCTION__.' param must be an array or null');
        }

        if (is_null($contentFilter)) {
            $contentFilter = array();
        }

        $this->contentFilter = $contentFilter;
    }

    /**
     * @return array List filter
     */
    protected function getListFilter()
    {
        return $this->listFilter;
    }

    /**
     * @param array $listFilter
     *
     * @return none
     */
    public function setListFilter($listFilter)
    {
        if (!is_array($listFilter) && !is_null($listFilter)) {
            throw new \InvalidArgumentException(__FUNCTION__.' param must be an array or null');
        }

        if (is_null($listFilter)) {
            $listFilter = array();
        }

        $this->listFilter = $listFilter;
    }

    /**
     * @return array Genre filter
     */
    protected function getGenreFilter()
    {
        return $this->genreFilter;
    }

    /**
     * @param array $genreFilter
     *
     * @return none
     */
    public function setGenreFilter($genreFilter)
    {
        if (!is_array($genreFilter) && !is_null($genreFilter)) {
            throw new \InvalidArgumentException(__FUNCTION__.' param must be an array or null');
        }

        if (is_null($genreFilter)) {
            $genreFilter = array();
        }

        $this->genreFilter = $genreFilter;
    }

    /**
     * @return string genreFilterMatchAny
     */
    public function getGenreFilterMatchAny()
    {
        return $this->genreFilterMatchAny;
    }

    /**
     * @param string $genreFilterMatchAny
     *
     * @return none
     */
    public function setGenreFilterMatchAny($genreFilterMatchAny)
    {
        if (!is_bool($genreFilterMatchAny)) {
            throw new \InvalidArgumentException(__FUNCTION__." param must have be a boolean");
        }

        $this->genreFilterMatchAny = $genreFilterMatchAny;
    }

    public function addItem($filmId, $saveToDb = false)
    {
        if (!is_numeric($filmId)) {
            throw new \InvalidArgumentException(__FUNCTION__.' param must be a number');
        }

        $errorFree = true;

        if (!in_array(intval($filmId), $this->listItems)) {
            $this->listItems[] = intval($filmId);

            if ($saveToDb) {
                if (! $this->addItemToDb($filmId)) {
                    $errorFree = false;
                }
            }
        }

        return $errorFree;
    }

    protected function addItemToDb($filmId)
    {
        $db = getDatabase();
        $errorFree = true;
        $isDuplicate = false;
        $prevItemUpdated = false;
        $username = $this->username;
        $listnameEscapedAndQuoted = DbConn::quoteOrNull( $this->listname, $db );
        $wherePrefix = " WHERE user_name='$username' AND listname=$listnameEscapedAndQuoted ";

        // Check for an duplicate
        $query = "SELECT count(1) as count FROM filmlist $wherePrefix" .
                    "   AND film_id=$filmId";
        $result = $db->query($query);
        $row = $result->fetch();
        if ($row["count"] > 0) {
            $isDuplicate = true;
        }

        // Update the current last item to point to the new item
        if ($errorFree && !$isDuplicate) {
            $query = "UPDATE filmlist SET next_film_id=$filmId $wherePrefix" .
                        "   AND next_film_id IS NULL";
            if (! $db->query($query)) {
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
                $errorFree = false;
            } else {
                $prevItemUpdated = true;
            }
        }

        // Insert the new item
        if ($errorFree && $prevItemUpdated) {
            // Get the new position (highest existing position + 1)
            $position = 1;
            $query = "SELECT position FROM filmlist $wherePrefix" .
                        " ORDER BY position DESC LIMIT 1";
            $result = $db->query($query);
            if ($result->rowCount() == 1) {
                $position = $result->fetch()["position"] + 1;
            }

            $columns = "user_name, film_id, listname, position, next_film_id, create_ts";
            $values = "'$username', $filmId, $listnameEscapedAndQuoted, $position, NULL, CURRENT_TIMESTAMP";
            $query = "INSERT INTO filmlist ($columns) VALUES ($values)";
            if (! $db->query($query)) {
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
                $errorFree = false;
                if ($prevItemUpdated) {
                    // Undo the update
                    $query = "UPDATE filmlist SET next_film_id=NULL $wherePrefix" .
                                "   AND next_film_id=$filmId";
                    $db->query($query);
                }
            }
        }

        return $errorFree;
    }

    public function removeItem($removeThisFilmId, $saveToDb = false)
    {
        $errorFree = true;

        if (in_array($removeThisFilmId, $this->listItems)) {
            $remainingItems = array();
            foreach ($this->listItems as $filmId) {
                if ($removeThisFilmId != $filmId) {
                    $remainingItems[] = $filmId;
                }
            }
            $this->listItems = $remainingItems;
        }
        
        if ($saveToDb && $errorFree) {
            $errorFree = $this->removeItemFromDb($removeThisFilmId);
        }

        return $errorFree;
    }

    protected function removeItemFromDb($removeThisFilmId)
    {
        $db = getDatabase();
        $errorFree = true;
        $rowExists = false;
        $username = $this->username;
        $listname = $this->listname;
        $listnameEscapedAndQuoted = DbConn::quoteOrNull( $listname, $db );
        $wherePrefix = " WHERE user_name='$username' AND listname=$listnameEscapedAndQuoted ";

        $query = "SELECT next_film_id, position FROM filmlist $wherePrefix" .
                    "   AND film_id=$removeThisFilmId";
        $result = $db->query($query);
        if ($result->rowCount() == 1) {
            $rowExists = true;
            $row = $result->fetch();
            $nextFilmId = $row["next_film_id"];
            if (empty($nextFilmId)) {
                $nextFilmId = "NULL";
            }
            $position = $row["position"];
        }

        if (! $rowExists) {
            return $errorFree;
        }

        // Update next_film_id for the item pointing to the removed item
        $nextIdUpdated = false;
        $query = "UPDATE filmlist SET next_film_id=$nextFilmId $wherePrefix" .
                    "   AND next_film_id=$removeThisFilmId";
        if ($db->query($query)) {
            $nextIdUpdated = true;
        } else {
            logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
            $errorFree = false;
        }
                
        // Update positions for all items after the item removed
        $positionsUpdated = false;
        if ($nextIdUpdated) {
            $query = "UPDATE filmlist SET position=position-1 $wherePrefix" .
                        "   AND position > $position";
            if ($db->query($query)) {
                $positionsUpdated = true;
            } else {
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
                $errorFree = false;
            }
        }

        // Undo the next_film_id update if $positionsUpdated failed
        if (! $positionsUpdated) {
            $query = "UPDATE filmlist SET next_film_id=$removeThisFilmId $wherePrefix" .
                        "   AND next_film_id=$nextFilmId";
            if ($db->query($query)) {
                $nextIdUpdated = false;
            } else {
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
                $errorFree = false;
            }
        }

        // Delete the removed item
        if ($nextIdUpdated && $positionsUpdated) {
            $query = "DELETE FROM filmlist $wherePrefix" .
                        "   AND film_id=$removeThisFilmId";
            logDebug("Delete filmlist item: " . $query, __CLASS__."::".__FUNCTION__." ".__LINE__);
            if (! $db->query($query)) {
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
                $errorFree = false;
            }
        }

        return $errorFree;
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

    public function createToDb()
    {
        if (empty($this->username) || empty($this->listname)) {
            throw new \InvalidArgumentException(__FUNCTION__." username (".$this->username.") and listName (".$this->listname.") must not be empty");
        }

        $db = getDatabase();
        $errorFree = true;
        $username = $this->username;
        $listname = $this->listname;
        $listnameEscapedAndQuoted = DbConn::quoteOrNull( $listname, $db );
        $parentListname = $this->parentListname;
        $parentListnameEscapedAndQuoted = DbConn::quoteOrNull( $parentListname, $db );

        // Error if the list already exists
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname=$listnameEscapedAndQuoted";
        $result = $db->query($query);
        if ($result->rowCount() > 1) {
            throw new \InvalidArgumentException(__FUNCTION__." username (".$this->username.") and listName (".$this->listname.") existing list cannot be created");
        }

        // Validate that the parent list is a existing list
        $parentListColumn = "";
        $parentListValue = "";
        if (!empty($parentListname)) {
            $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname=$parentListnameEscapedAndQuoted";
            $result = $db->query($query);
            if ($result->rowCount() == 1) {
                $parentListColumn = ", parent_listname";
                $parentListValue = ", $parentListnameEscapedAndQuoted";
            } else {
                logDebug("Warning: Creating filmlist $listnameEscapedAndQuoted with parent $parentListnameEscapedAndQuoted.\nParent does not exist. Continuing to create the list without a parent.", __CLASS__."::".__FUNCTION__." ".__LINE__);
                $errorFree = false;
            }
        }

        // Insert filmlist
        $query = "INSERT INTO user_filmlist (user_name, listname, create_ts".$parentListColumn.")" .
                    " VALUES ('$username', $listnameEscapedAndQuoted, CURRENT_TIMESTAMP".$parentListValue.")";
        logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
        if (! $db->query($query)) {
            logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
            $errorFree = false;
        }

        // Insert list items
        $length = count($this->listItems);
        for ($i=0; $i < $length; $i++) {
            $filmId = $this->listItems[$i];
            $position = $i+1;
            $nextFilmId = "NULL";
            if ($i+1 < $length) {
                // Not last item
                $nextFilmId = $this->listItems[$i+1];
            }

            $query = "INSERT INTO filmlist (user_name, listname, position, film_id, next_film_id, create_ts)" .
                     " VALUES ('$username', $listnameEscapedAndQuoted, $position, $filmId, $nextFilmId, CURRENT_TIMESTAMP)";
            logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
            if (! $db->query($query)) {
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
                $errorFree = false;
            }
        }

        return $errorFree;
    }

    /**
     * Delete from the DB. If the list has children and this fails.
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
        $listnameEscapedAndQuoted = DbConn::quoteOrNull( $listname, $db );
        $query = "SELECT * FROM user_filmlist" .
                    " WHERE parent_listname=$listnameEscapedAndQuoted" .
                    "   AND user_name='$username'";
        $result = $db->query($query);
        if ($result->rowCount() > 0) {
            $success = false;
        } else {
            // Delete all entries from this list
            $query = "DELETE FROM filmlist" .
                        " WHERE user_name='$username'" .
                        " AND listname=$listnameEscapedAndQuoted";
            logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
            if (! $db->query($query)) {
                $success = false;
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
            }
        
            // Delete the list itself
            $query = "DELETE FROM user_filmlist WHERE user_name='$username' AND listname=$listnameEscapedAndQuoted";
            if (! $db->query($query)) {
                $success = false;
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
            }
        }

        return $success;
    }

    /**
     * @return array result["Success"] true if the list and all sublists are deleted.
     *               result["Success"] false if the list or any of its sublists fail.
     *               result["Success"] false if removeSubLists=false and the list has sublists.
     *               result["DeletedLists"] an array listname of any list get deleted.
     *                  It is possible that some of the sublists are deleted, but not
     *                  all (unlikely). In that case Success=false and DeletedLists shows
     *                  the lists that did get deleted.
     */
    public static function removeListFromDb($username, $listname, $removeSubLists = false)
    {
        $list = new Filmlist($username, $listname);
        $success = true;
        $deletedLists = array();

        if ($removeSubLists) {
            $sublists = $list->getUserListsFromDbByParent($username, false, $list->getListname());
            foreach ($sublists as $sublistArr) {
                $sublist = new Filmlist($username, $sublistArr["listname"], $listname);
                $result = Filmlist::removeListFromDb($username, $sublist->getListname(), true);
                
                $deletedLists = array_merge($deletedLists, $result["DeletedLists"]);
                if ($result["Success"] != "true") {
                    $success = false;
                    break;
                }
            }
        }
        
        if ($success) {
            $success = $list->removeFromDb();
            if ($success) {
                $deletedLists[] = $list->getListname();
            }
        }

        $successStr = "false";
        if ($success) {
            $successStr = "true";
        }
        $result = array("Success" => $successStr, "DeletedLists" => $deletedLists);

        return $result;
    }

    public function renameToDb($newListname) {
        if (empty($this->username) || empty($this->listname)) {
            throw new \InvalidArgumentException(__FUNCTION__." username (".$this->username.") and listName (".$this->listname.") must not be empty");
        }
        $username = $this->username;

        $db = getDatabase();
        $success = true;

        $oldListname = $this->listname;
        $oldListnameEscapedAndQuoted = DbConn::quoteOrNull( $oldListname, $db );
        $newListnameEscapedAndQuoted = DbConn::quoteOrNull( $newListname, $db );
        
        // Rename the listname on the list itself
        $query  = "UPDATE user_filmlist SET listname=$newListnameEscapedAndQuoted";
        $query .= " WHERE user_name='$username'";
        $query .= "   AND listname=$oldListnameEscapedAndQuoted";
        logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
        if (! $db->query($query)) {
            $success = false;
            logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
        }

        // Rename the listname on the items in the list
        if ($success) {
            $query  = "UPDATE filmlist SET listname=$newListnameEscapedAndQuoted";
            $query .= " WHERE user_name='$username'";
            $query .= "   AND listname=$oldListnameEscapedAndQuoted";
            logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
            if (! $db->query($query)) {
                $success = false;
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
            }
        }

        // Update the parent name for sublists
        if ($success) {
            $query  = "UPDATE user_filmlist SET parent_listname=$newListnameEscapedAndQuoted";
            $query .= " WHERE user_name='$username'";
            $query .= "   AND parent_listname=$oldListnameEscapedAndQuoted";
            logDebug($query, __CLASS__."::".__FUNCTION__." ".__LINE__);
            if (! $db->query($query)) {
                $success = false;
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
            }
        }

        return $success;
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

        $listnameEscapedAndQuoted = DbConn::quoteOrNull( $listname, $db );

        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname=$listnameEscapedAndQuoted";
        $result = $db->query($query);
        if ($result->rowCount() == 1) {
            $this->parentListname = $result->fetch()['parent_listname'];
        }
        
        $queryTables = "filmlist";
        $usingFilmTable = false;

        $contentTypeFilterWhere = "";
        $contentTypeFilteredOut = $this->getContentTypeFilterCommaDelimited();
        if (!empty($contentTypeFilteredOut)) {
            $queryTables .= ", film";
            $usingFilmTable = true;
            $contentTypeFilterWhere .= " AND filmlist.film_id=film.id ";
            $contentTypeFilterWhere .= " AND contentType NOT IN (" . $contentTypeFilteredOut . ") ";
        }

        $listFilterWhere = "";
        $listsFilteredIn = $this->getListFilterCommaDelimited();
        if (!empty($listsFilteredIn)) {
            $listFilterWhere = " AND filmlist.film_id IN (SELECT DISTINCT(film_id) as id2 FROM filmlist as list2 WHERE user_name='$username' AND listname IN ($listsFilteredIn)) ";
        }

        $genreFilterWhere = "";
        if (count($this->getGenreFilter()) > 0) {
            if ($this->getGenreFilterMatchAny()) {
                $genresFilteredIn = $this->getGenreFilterCommaDelimited();
                $genreFilterWhere .= " AND filmlist.film_id IN (SELECT DISTINCT(film_id) as id3 FROM film_genre WHERE genre_name IN ($genresFilteredIn)) ";
            } else {
                foreach ($this->getGenreFilter() as $genre) {
                    $genreFilterWhere .= " AND EXISTS (SELECT * FROM film_genre WHERE filmlist.film_id=film_genre.film_id AND genre_name='$genre') ";
                }
            }
        }
        
        $query  = "SELECT film_id FROM $queryTables";
        $query .= " WHERE user_name='$username'";
        $query .= "   AND listname=$listnameEscapedAndQuoted";
        $query .=     $contentTypeFilterWhere;
        $query .=     $listFilterWhere;
        $query .=     $genreFilterWhere;

        $result = $db->query($query);
        $filteredFilmIds = array();
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $filteredFilmIds[] = $filmId;
        }

        $sortedFilmIds = null;
        if ($this->getSort() == ListSortField::position) {
            $sortedFilmIds = $this->getItemsByNextId($this->getSortDirection());
        } else {
            $sortedFilmIds = $this->getItemsByCreate($this->getSortDirection());
        }

        foreach ($sortedFilmIds as $filmId) {
            if (in_array($filmId, $filteredFilmIds)) {
                $this->listItems[] = $filmId;
            }
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
        foreach ($result->fetchAll() as $row) {
            $listname = $row['listname'];
            $list = new Filmlist($username, $listname);
            $list->initFromDb();
            $lists[$listname] = $list;
        }

        $query = "SELECT * FROM filmlist WHERE user_name='$username' ORDER BY listname ASC, position ASC";
        $result = $db->query($query);
        foreach ($result->fetchAll() as $row) {
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
                $dbList->removeItem($filmId, true);
            }

            $dbListnames[] = $dbList->getListname();
        }

        // Add items from the object that the dbLists do not have
        foreach ($objectListnames as $name) {
            if (!in_array($name, $dbListnames)) {
                $newList = new Filmlist($username, $name);
                $newList->addItem($filmId, true);
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

        $parentListnameEscapedAndQuoted = null;
        $parentSelect = "parent_listname IS NULL";
        if (!empty($parentListname)) {
            $parentListnameEscapedAndQuoted = DbConn::quoteOrNull( $parentListname, $db );
            $parentSelect = "parent_listname=$parentListnameEscapedAndQuoted";
        }

        // From the DB build a flat array with listname the keys and parentList as the values
        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND $parentSelect ORDER BY listname ASC";
        $result = $db->query($query);
        foreach ($result->fetchAll() as $row) {
            $listname = $row['listname'];
            $parentListnameDb = $row['parent_listname'];
            $items = array();
            if ($populateFilms) {
                $filmlist = new Filmlist($username, $listname, $parentListnameDb);
                $filmlist->initFromDb();
                $items = $filmlist->getItems();
            }
            $children = self::getUserListsFromDbByParent($username, $populateFilms, $listname);
            $listnames[] = array("listname" => $listname, "parentListname" => $parentListnameDb, "items" => $items, "children" => $children);
        }

        return $listnames;
    }

    public function getContentTypeFilterCommaDelimited(): string
    {
        $filteredOut = "";
        $comma = "";
        reset($this->contentFilter);

        $iter = (new ArrayObject($this->contentFilter))->getIterator();
        while ($iter->valid()) {

            $current = $iter->current();
            if ( $current !== false && Film::validContentType($current) ) {
                $filteredOut .= $comma . "'$current'";
                $comma = ", ";
            }

            $iter->next();
        }

        return $filteredOut;
    }

    public function getListFilterCommaDelimited() {
        $commaDelimitedLists = "";
        $comma = "";
        foreach ($this->listFilter as $item) {
            $commaDelimitedLists .= $comma . "'$item'";
            $comma = ", ";
        }
        
        return $commaDelimitedLists;
    }

    public function getGenreFilterCommaDelimited() {
        $commaDelimitedLists = "";
        $comma = "";
        foreach ($this->genreFilter as $item) {
            $commaDelimitedLists .= $comma . "'$item'";
            $comma = ", ";
        }
        
        return $commaDelimitedLists;
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
            $listnameEscapedAndQuoted = DbConn::quoteOrNull( $listname, $db );
            $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname=$listnameEscapedAndQuoted";
            $result = $db->query($query);
            if ($result->rowCount() == 1) {
                $parentListname = $result->fetch()['parent_listname'];
                if (!empty($parentListname)) {
                    $parentListnameEscapedAndQuoted = DbConn::quoteOrNull( $parentListname, $db );
                    $parentListnameEscaped = unquote($parentListnameEscapedAndQuoted);
                    $ancestors[] = $parentListnameEscaped;
                }
                $listname = $parentListname;
            }
        }

        return $ancestors;
    }

    public function moveItem($myFilmId, $nextFilmId, $saveToDb = false)
    {
        $errorFree = true;
        
        // Do nothing if it will not move. This not a error.
        if ($myFilmId == $nextFilmId) {
            return $errorFree;
        }
        
        $items = $this->listItems;
        $inList = in_array($myFilmId, $items);
        $nextFilmIdValid = in_array($nextFilmId, $items) || $nextFilmId == -1;
        if ($inList && $nextFilmIdValid) {
            // Build a new list one item at a time. Add my item before nextFilmId
            // and remove my original location.
            $newListItems = array();
            $madeAChange = false;
            while ($currentFilmId = array_shift($items)) {
                if ($currentFilmId == $nextFilmId) {
                    $newListItems[] = $myFilmId;
                    $newListItems[] = $nextFilmId;
                    $madeAChange = true;
                } elseif ($currentFilmId == $myFilmId) {
                    // Doing nothing removes this item in this location
                    $madeAChange = true;
                } else {
                    $newListItems[] = $currentFilmId;
                }
            }
        
            // Param nextFilmId -1 goes to the end
            if ($nextFilmId == -1) {
                $newListItems[] = $myFilmId;
            }

            // Replace items with the new list
            if ($madeAChange) {
                $this->listItems = $newListItems;
            } else {
                $errorFree = false;
            }
        } else {
            $errorFree = false;
        }

        
        if ($saveToDb) {
            $dbErrorFree = $this->moveItemInDb($myFilmId, $nextFilmId);
            if (! $dbErrorFree) {
                $errorFree = false;
            }
        }

        return $errorFree;
    }

    protected function moveItemInDb($myFilmId, $nextFilmId)
    {
        $errorFree = true;

        // Do nothing if it will not move
        if ($myFilmId == $nextFilmId) {
            return $errorFree;
        }
        
        $newPrevFilmId = null;
        $origPrevFilmId = null;
        $origNextFilmId = "NULL";

        $db = getDatabase();
        $wherePrefix = " WHERE user_name='" . $this->username . "'" .
                    "   AND listname=" . DbConn::quoteOrNull( $this->listname, $db );
        $queryPrefix = "SELECT film_id FROM filmlist" . $wherePrefix; 
        $newPrevQuery = $queryPrefix . "AND next_film_id=$nextFilmId";    

        // Validate that myFilmID and nextFilmId are in the db 
        $filmIdsValid = false;
        if ($nextFilmId != -1) {
            $result = $db->query($queryPrefix . "AND (film_id=$myFilmId OR film_id=$nextFilmId)");
            if ($result->rowCount() == 2) {
                $filmIdsValid = true;
            }
        } else {
            $newPrevQuery = $queryPrefix . "AND next_film_id IS NULL";

            $result = $db->query($queryPrefix . "AND film_id=$myFilmId");
            if ($result->rowCount() == 1) {
                $filmIdsValid = true;
            }
        }
        if (! $filmIdsValid) {
            $errorFree = false;
            return $errorFree;
        }

        $result = $db->query($newPrevQuery);
        if ($result->rowCount() == 1) {
            $newPrevFilmId = $result->fetch()['film_id'];
        }
        $result = $db->query($queryPrefix . "AND next_film_id=$myFilmId");
        if ($result->rowCount() == 1) {
            $origPrevFilmId = $result->fetch()['film_id'];
        }
        $result = $db->query("SELECT next_film_id FROM filmlist" . $wherePrefix . "AND film_id=$myFilmId");
        if ($result->rowCount() == 1) {
            $origNextFilmId = $result->fetch()['next_film_id'];
            if (empty($origNextFilmId)) {
                $origNextFilmId = "NULL";
            }
        }

        // Do nothing if I am already in the new location
        if ($newPrevFilmId == $myFilmId) {
            return $errorFree;
        }

        // Set my original previous item to point to my original next item
        if (! empty($origPrevFilmId))
        {
            $query = "UPDATE filmlist SET next_film_id=$origNextFilmId" .
                        $wherePrefix . " AND film_id=$origPrevFilmId";
            if (! $db->query($query)) {
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
                $errorFree = false;
            }
        }

        // Set the new previous item to point to me as the next item
        if (! empty($newPrevFilmId))
        {
            $query = "UPDATE filmlist SET next_film_id=$myFilmId" .
                        $wherePrefix . " AND film_id=$newPrevFilmId";
            if (! $db->query($query)) {
                logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
                $errorFree = false;
            }
        }

        // Set my item to point to the new next item
        $nextFilmIdSet = "next_film_id=$nextFilmId";
        if ($nextFilmId == -1) {
            $nextFilmIdSet = "next_film_id=NULL";
        }
        $query = "UPDATE filmlist SET $nextFilmIdSet" .
                    $wherePrefix . " AND film_id=$myFilmId";
        if (! $db->query($query)) {
            logDebug($query."\nSQL Error (".$db->errorCode().") ".$db->errorInfo()[2], __CLASS__."::".__FUNCTION__." ".__LINE__);
            $errorFree = false;
        }

        return $errorFree;
    }

    public function getItemsByNextId($sortDirection) {
        $username = $this->getUsername();
        $db = getDatabase();
        
        $listnameEscapedAndQuoted = DbConn::quoteOrNull( $this->getListname(), $db );

        $query  = "SELECT film_id, next_film_id FROM filmlist";
        $query .= " WHERE user_name='$username'";
        $query .= "   AND listname=$listnameEscapedAndQuoted";
        $result = $db->query($query);
        
        $items = array(); // Keys are next_film_id, Values are film_id
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $next = $row['next_film_id'];
            $items[$next] = intval($filmId);
        }
        
        $orderedItems = array();
        $nextFilmId = "";
        while (array_key_exists($nextFilmId, $items)) {
            $filmId = $items[$nextFilmId];
            $orderedItems[] = intval($filmId);

            $nextFilmId = $filmId;
        }
            
        if ($sortDirection == SqlSortDirection::ascending) {
            $orderedItems = array_reverse($orderedItems);
        }

        return $orderedItems;
    }

    public function getItemsByCreate($sortDirection) {
        if (empty($sortDirection)) {
            $sortDirection = SqlSortDirection::ascending;
        }
        $username = $this->getUsername();
        $db = getDatabase();
        $listnameEscapedAndQuoted = DbConn::quoteOrNull( $this->getListname(), $db );

        $query  = "SELECT film_id, next_film_id FROM filmlist";
        $query .= " WHERE user_name='$username'";
        $query .= "   AND listname=$listnameEscapedAndQuoted";
        $query .= " ORDER BY create_ts $sortDirection->value";
        $result = $db->query($query);
        
        $items = array(); // Keys are next_film_id, Values are film_id
        foreach ($result->fetchAll() as $row) {
            $items[] = $row['film_id'];
        }

        return $items;
    }
}

enum ListSortField: string {
    case position   = 'Position';
    case modified   = 'Added';
    public function validated(): bool {
        return in_array($this, [self::position, self::modified]);
    }

    static public function convert(string $sortField): ListSortField | null {
        $result =  ListSortField::tryFrom($sortField);

        if ($result === null) {
            if ( $sortField == 'pos' ) {
                $result = ListSortField::position;
            }
            else if ( $sortField == 'mod' ) {
                $result = ListSortField::modified;
            }
        }

        return $result;
    }
}