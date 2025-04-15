<?php
/**
 * Temp PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Filmlist.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Film.php";

require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

const TEST_LIST = Constants::LIST_DEFAULT;
const TEST_NEW_LIST = "newlist";

class FilmlistTest extends RatingSyncTestCase
{
    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    /**
     * @covers \RatingSync\Filmlist::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Filmlist::setListName
     * @covers  \RatingSync\Filmlist::getListName
     * @depends testObjectCanBeConstructed
     */
    public function testSetAndGetListName()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        
        // Int
        $list->setListName(1234);
        $this->assertEquals(1234, $list->getListName());
        
        // Number as a string
        $list->setListName("1234");
        $this->assertEquals(1234, $list->getListName());
        
        // Alpha-num string
        $list->setListName("List 1D");
        $this->assertEquals("List 1D", $list->getListName());
    }

    /**
     * @covers  \RatingSync\Filmlist::setListName
     * @depends testObjectCanBeConstructed
     */
    public function testSetListNameNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        
        // Null
        $list->setListName(null);
    }

    /**
     * @covers  \RatingSync\Filmlist::setListName
     * @depends testObjectCanBeConstructed
     */
    public function testSetListNameEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);

        // Empty String
        $list->setListName("");
    }

    /**
     * @covers  \RatingSync\Filmlist::setUsername
     * @covers  \RatingSync\Filmlist::getUsername
     * @depends testObjectCanBeConstructed
     */
    public function testSetAndGetUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        
        // Int
        $list->setUsername(1234);
        $this->assertEquals(1234, $list->getUsername());
        
        // Number as a string
        $list->setUsername("1234");
        $this->assertEquals(1234, $list->getUsername());
        
        // Alpha-num string
        $list->setUsername("Username 1D");
        $this->assertEquals("Username 1D", $list->getUsername());
    }

    /**
     * @covers  \RatingSync\Filmlist::setUsername
     * @depends testObjectCanBeConstructed
     */
    public function testSetUsernameNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        
        // Null
        $list->setUsername(null);
    }

    /**
     * @covers  \RatingSync\Filmlist::setUsername
     * @depends testObjectCanBeConstructed
     */
    public function testSetUsernameEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);

        // Empty String
        $list->setUsername("");
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @depends testObjectCanBeConstructed
     */
    public function testAddItemWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(null);
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @depends testObjectCanBeConstructed
     */
    public function testAddItemWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\InvalidArgumentException::class);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem("");
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @depends testObjectCanBeConstructed
     */
    public function testAddItem()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @depends testObjectCanBeConstructed
     */
    public function testAddItemAddSecondItem()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @depends testObjectCanBeConstructed
     */
    public function testAddItemDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(100);

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @depends testObjectCanBeConstructed
     */
    public function testAddItemMultiWithDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $list->addItem(200);

        $this->assertTrue(true); // Making sure we made it this far
    }    

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @covers  \RatingSync\Filmlist::getItems
     * @depends testObjectCanBeConstructed
     * @depends testAddItem
     */
    public function testGetItems()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $this->assertEquals(array('100'), $list->getItems());
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @covers  \RatingSync\Filmlist::getItems
     * @depends testObjectCanBeConstructed
     * @depends testAddItemAddSecondItem
     */
    public function testGetItemsTwoItems()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $this->assertEquals(array('100', '200'), $list->getItems());
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @covers  \RatingSync\Filmlist::getItems
     * @depends testObjectCanBeConstructed
     * @depends testAddItemDuplicate
     */
    public function testGetItemsDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(100);
        $this->assertEquals(array('100'), $list->getItems());
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @covers  \RatingSync\Filmlist::getItems
     * @depends testObjectCanBeConstructed
     * @depends testAddItemMultiWithDuplicate
     */
    public function testGetItemsMultiWithDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $list->addItem(100);
        $this->assertEquals(array('100', '200'), $list->getItems());
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @covers  \RatingSync\Filmlist::getItems
     * @depends testObjectCanBeConstructed
     * @depends testAddItem
     */
    public function testGetItemsThreeItems()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $list->addItem(300);
        $this->assertEquals(array('100', '200', '300'), $list->getItems());
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @covers  \RatingSync\Filmlist::getItems
     * @depends testObjectCanBeConstructed
     * @depends testAddItem
     */
    public function testAddItemThreeAddSecondAgain()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $list->addItem(300);
        $list->addItem(200);  //Keep order
        $this->assertEquals(array('100', '200', '300'), $list->getItems());
    }

    /**
     * @covers  \RatingSync\Filmlist::removeItem
     * @depends testObjectCanBeConstructed
     * @depends testGetItemsThreeItems
     */
    public function testRemoveItem()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $list->addItem(300);
        $list->removeItem(200);
        $this->assertEquals(array('100', '300'), $list->getItems());
    }

    /**
     * @covers  \RatingSync\Filmlist::removeItem
     * @depends testObjectCanBeConstructed
     * @depends testGetItemsThreeItems
     */
    public function testRemoveItemWithMissingItem()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $list->addItem(300);
        $list->removeItem(400);
        $this->assertEquals(array('100', '200', '300'), $list->getItems());
    }

    /**
     * @covers  \RatingSync\Filmlist::removeAllItems
     * @depends testObjectCanBeConstructed
     * @depends testGetItemsThreeItems
     */
    public function testRemoveAllItems()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $list->addItem(300);
        $list->removeAllItems();
        $this->assertEmpty($list->getItems());
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @covers  \RatingSync\Filmlist::inList
     * @depends testObjectCanBeConstructed
     * @depends testAddItem
     */
    public function testinListTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $list->addItem(300);
        $this->assertTrue($list->inList(200));
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @covers  \RatingSync\Filmlist::inList
     * @depends testObjectCanBeConstructed
     * @depends testAddItem
     */
    public function testinListFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $list->addItem(300);
        $this->assertFalse($list->inList(400));
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @covers  \RatingSync\Filmlist::removeAllItems
     * @covers  \RatingSync\Filmlist::inList
     * @depends testObjectCanBeConstructed
     * @depends testRemoveAllItems
     * @depends testinListTrue
     */
    public function testRemoveAllItemsThenAddOne()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(100);
        $list->addItem(200);
        $list->addItem(300);
        $list->removeAllItems();
        $list->addItem(100);
        $this->assertTrue($list->inList(100));
    }
    
    public static function setupFilms()
    {
        $db = getDatabase();
        $errorFree = true;
        
        DatabaseTest::resetDb();
        
        $film = new Film();
        $film->setTitle("Filmlist Title 1");
        $film->setYear(2016);
        if (! $film->saveToDb()) {
            $errorFree = false;
        }
        
        $film = new Film();
        $film->setTitle("Filmlist Title 2");
        $film->setYear(2016);
        if (! $film->saveToDb()) {
            $errorFree = false;
        }
        
        $film = new Film();
        $film->setTitle("Filmlist Title 3");
        $film->setYear(2016);
        if (! $film->saveToDb()) {
            $errorFree = false;
        }
        
        $film = new Film();
        $film->setTitle("Filmlist Title 4");
        $film->setYear(2016);
        if (! $film->saveToDb()) {
            $errorFree = false;
        }

        return $errorFree;
    }

    public function testSetup()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(self::setupFilms(), "setupFilms() failed");
    }

    /**
     * - Add 3 items
     *
     * Expect
     *   - Return true (errorFree == true)
     *   - New row in table: user_filmlist
     *   - 3 new rows in table: filmlist
     *   - Items in the order 1, 2, 3
     *
     * @covers  \RatingSync\Filmlist::createToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetup
     * @depends testAddItem
     */
    public function testCreateToDb()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = TEST_NEW_LIST;
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);

        // Test
        $errorFree = $list->createToDb();

        // Verify
        $this->assertTrue($errorFree, "createToDb() should return true (error free)");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY create_ts ASC";
        $result = $db->query($query);
        $this->assertEquals(3, $result->rowCount(), "Should be 3 films in the list");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' AND next_film_id=2";
        $result = $db->query($query);
        $this->assertEquals(1, $result->fetch()['film_id'], "First item should be filmId 1");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' AND next_film_id=3";
        $result = $db->query($query);
        $this->assertEquals(2, $result->fetch()['film_id'], "Second item should be filmId 2");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' AND next_film_id IS NULL";
        $result = $db->query($query);
        $this->assertEquals(3, $result->fetch()['film_id'], "Last item should be filmId 3");
    }

    /**
     * Expect
     *   - New row in table: user_filmlist
     *   - Return true (errorFree == true)
     *
     * @covers  \RatingSync\Filmlist::createToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetup
     * @depends testCreateToDb
     */
    public function testCreateToDbNoItems()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = TEST_NEW_LIST . "_NoItems";
        $list = new Filmlist($username, $listname);

        // Test
        $errorFree = $list->createToDb();

        // Verify
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY create_ts ASC";
        $result = $db->query($query);
        $this->assertEquals(0, $result->rowCount(), "Should be no films in the list");
        $this->assertTrue($errorFree, "createToDb() should return true (error free)");
    }

    /**
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetItems
     */
    public function testInitFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testInitFromDb";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->createToDb();

        // Test
        $listDb = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $listDb->setSort(ListSortField::position);
        $listDb->setSortDirection(SqlSortDirection::ascending);
        $listDb->initFromDb();

        // Verify
        $this->assertEquals($list->getItems(), $listDb->getItems(), "Original items should match items from db");
    }

    /**
     * - Construct 2 lists with items
     * - Call createToDb() for both lists
     * - Retrieve the lists from the db
     * - Sort both lists from the db (sort by numerically)
     *
     * Expect
     *   - Items match from the original list1 and from db (after sorting)
     *   - Items match from the original list2 and from db (after sorting)
     *
     * @covers  \RatingSync\Filmlist::createToDb
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetItems
     */
    public function testCreateToDbSecondList()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $listname1 = "list1";
        $listname2 = "list2";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname1);
        $list->addItem(1);
        $list->addItem(2);
        $list2 = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname2);
        $list2->addItem(3);

        // Test
            // List 1
        $list->createToDb();
        $list2->createToDb();

        // Verify
            // List 1
        $listDb1 = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname1);
        $listDb1->setSort(ListSortField::position);
        $listDb1->setSortDirection(SqlSortDirection::ascending);
        $listDb1->initFromDb();
        $this->assertEquals($list->getItems(), $listDb1->getItems(), "First list should match");
            // List 2
        $listDb2 = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname2);
        $listDb2->setSort(ListSortField::position);
        $listDb2->setSortDirection(SqlSortDirection::ascending);
        $listDb2->initFromDb();
        $this->assertEquals($list2->getItems(), $listDb2->getItems(), "Seconds list should match");
    }

    /**
     * @covers  \RatingSync\Filmlist::createToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetup
     * @depends testCreateToDb
     */
    public function testCreateToDbExistingListname()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        $this->expectException(\InvalidArgumentException::class);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = TEST_NEW_LIST;
        $list = new Filmlist($username, $listname);

        // Test
        $list->createToDb();
    }

    /**
     * - Username not in the db
     *
     * Expect
     *   - Return false (errorFree == false)
     *   - No new row in user_filmlist
     *
     * @covers  \RatingSync\Filmlist::createToDb
     * @depends testAddItem
     */
    public function testCreateToDbUsernameNotFound()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $listname = "testCreateToDbUsernameNotFound";
        $username = "bad_username";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);

        // Test
        $success = $list->createToDb();

        // Verify
        $this->assertFalse($success, "Should not succeed");
        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname='$listname'";
        $result = $db->query($query);
        $this->assertEquals(0, $result->rowCount(), "List ($listname) should not be inserted");
    }

    /**
     * - Parent already exists
     *
     * Expect
     *   - New row in table: user_filmlist
     *   - Value in the row's parent_listname matches
     *   - Return true (errorFree == true)
     *
     * @covers  \RatingSync\Filmlist::createToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetup
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testCreateToDbNoItems
     */
    public function testCreateToDbWithParent()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = TEST_NEW_LIST . "_WithParent";
        $parent = TEST_NEW_LIST;
        $list = new Filmlist($username, $listname);
        $list->setParentListname($parent);

        // Test
        $errorFree = $list->createToDb();

        // Verify
        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname='$listname' AND parent_listname='$parent'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount(), "List $listname should be have a parent ($parent)");
        $this->assertEquals($parent, $result->fetch()['parent_listname'], "user_filmlist.parent_listname should be '$parent'");
        $this->assertTrue($errorFree, "createToDb() should return true (error free)");
    }

    /**
     * - Set the list's parent listname
     * - Parent listname does not exist in the DB
     * - 2 items in the new list
     *
     * Expect
     *   - New row in table: user_filmlist
     *   - parent_listname is NULL in the new row
     *   - No row inserted for the parent
     *   - 2 new rows in table: filmlist
     *   - Return false (errorFree == false)
     *
     * @covers  \RatingSync\Filmlist::createToDb
     * @depends testObjectCanBeConstructed
     * @depends testAddItem
     * @depends testCreateToDb
     */
    public function testCreateToDbParentDoesNotExist()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = TEST_NEW_LIST . "_ParentDoesNotExist";
        $parent = "BadParent";
        $list = new Filmlist($username, $listname);
        $list->setParentListname($parent);
        $list->addItem(1);
        $list->addItem(2);

        // Test
        $errorFree = $list->createToDb();

        // Verify
        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname='$listname'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount(), "user_filmlist should be inserted");
        $this->assertEquals("", $result->fetch()['parent_listname'], "user_filmlist.parent_listname should be NULL");
        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname='$parent'";
        $result = $db->query($query);
        $this->assertEquals(0, $result->rowCount(), "There should not be a user_filmlist row of the parent ($parent)");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname'";
        $result = $db->query($query);
        $this->assertEquals(2, $result->rowCount(), "2 filmlist rows (list items) should be inserted");
        $this->assertFalse($errorFree, "createToDb() should return false (error free = false)");
    }
    
    /**
     * - List includes one item that doesn't match film.id in the DB
     * - List includes one item that does match film.id in the DB
     *
     * Expect
     *   - New row in user_filmlist
     *   - 1 new row in filmlist (not 2 new rows)
     *   - The new filmlist row film_id matches the good one
     *   - Return false (errorFree == false)
     *
     * @covers  \RatingSync\Filmlist::createToDb
     * @depends testObjectCanBeConstructed
     * @depends testAddItem
     * @depends testCreateToDb
     */
    public function testCreateToDbBadFilmId()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = TEST_NEW_LIST . "_BadFilmId";
        $badFilmId = 500;
        $goodFilmId = 2;
        $list = new Filmlist($username, $listname);
        $list->addItem($badFilmId);
        $list->addItem($goodFilmId);

        // Test
        $errorFree = $list->createToDb();

        // Verify
        $query = "SELECT * FROM user_filmlist WHERE user_name='$username' AND listname='$listname'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount(), "user_filmlist should be inserted");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname'";
        $result = $db->query($query);
        $this->assertEquals(1, $result->rowCount(), "Should be exactly 1 filmlist inserted");
        $this->assertEquals($goodFilmId, $result->fetch()['film_id'], "filmlist.film_id should be $goodFilmId");
        $this->assertFalse($errorFree, "createToDb() should return false (error free = false)");
    }

    /**
     * - List exists in the db with FilmIds 1, 2
     * - Add 3 with param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 1, 2, 3
     *   - next_film_id values 1->2, 2->3, 3->NULL
     *   - Position values (film_id/position) 1/1, 2/2, 3/3
     *
     * @covers  \RatingSync\Filmlist::addItem()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testAddItemToDb()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testAddItemToDb";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->createToDb();

        // Test
        $success = $list->addItem(3, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 2, 3), $items, "Items should be 1, 2, 3 (The new one is 3)");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(1 => array("pos" => "", "next" => ""), 2 => array("pos" => "", "next" => ""), 3 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[1]["pos"], "Id 1 position should be 1");
        $this->assertEquals(2, $itemsArr[1]["next"], "Id 1 next item should 2");
        $this->assertEquals(2, $itemsArr[2]["pos"], "Id 2 position should be 2");
        $this->assertEquals(3, $itemsArr[2]["next"], "Id 2 next item should 3");
        $this->assertEquals(3, $itemsArr[3]["pos"], "Id 3 position should be 3");
        $this->assertEquals(null, $itemsArr[3]["next"], "Id 3 next item should null");
    }

    /**
     * - List exists in the db with FilmIds 1, 2
     * - Add 3 with param saveToDb=default
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 1, 2
     *   - next_film_id values 1->2, 2->NULL
     *   - Position values (film_id/position) 1/1, 2/2
     *
     * @covers  \RatingSync\Filmlist::addItem()
     * @depends testAddItem
     * @depends testAddItemToDb
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testAddItemToDbDefault()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testAddItemToDbDefault";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->createToDb();

        // Test
        $success = $list->addItem(3);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 2), $items, "Items should be 1, 2 (FilmId 3 should not be saved)");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(1 => array("pos" => "", "next" => ""), 2 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[1]["pos"], "Id 1 position should be 1");
        $this->assertEquals(2, $itemsArr[1]["next"], "Id 1 next item should 2");
        $this->assertEquals(2, $itemsArr[2]["pos"], "Id 2 position should be 2");
        $this->assertEquals(null, $itemsArr[2]["next"], "Id 2 next item should null");
    }

    /**
     * - Add 3 to a listname that does not exist in the db
     * - Use param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 3
     *   - next_film_id values 3->NULL
     *   - Position values (film_id/position) 3/1
     *
     * @covers  \RatingSync\Filmlist::addItem()
     * @depends testAddItemToDb
     * @depends testInitFromDb
     */
    public function testAddItemToDbListDoesNotExist()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testAddItemToDbListDoesNotExist";
        $list = new Filmlist($username, $listname);

        // Test
        $success = $list->addItem(3, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(3), $items, "Item should be 3");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $row = $result->fetch();
        $this->assertEquals(1, $row['position'], "Id 3 position should be 1");
        $this->assertEquals(null, $row['next_film_id'], "Id 3 next item should null");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3
     * - Add 100 with param saveToDb=true
     *
     * Expect
     *   - Returns false (errors)
     *   - After initFromDb() items are 1, 2, 3
     *   - next_film_id values 1->2, 2->3, 3->NULL
     *   - Position values (film_id/position) 1/1, 2/2, 3/3
     *
     * @covers  \RatingSync\Filmlist::addItem()
     * @depends testAddItem
     * @depends testAddItemToDb
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testAddItemToDbFilmDoesNotExist()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testAddItemToDbFilmDoesNotExist";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->createToDb();

        // Test
        $success = $list->addItem(100, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertFalse($success, "The function should has error(s)");
        $this->assertEquals(array(1, 2, 3), $items, "Items should be 1, 2, 3 (No new one)");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(1 => array("pos" => "", "next" => ""), 2 => array("pos" => "", "next" => ""), 3 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[1]["pos"], "Id 1 position should be 1");
        $this->assertEquals(2, $itemsArr[1]["next"], "Id 1 next item should 2");
        $this->assertEquals(2, $itemsArr[2]["pos"], "Id 2 position should be 2");
        $this->assertEquals(3, $itemsArr[2]["next"], "Id 2 next item should 3");
        $this->assertEquals(3, $itemsArr[3]["pos"], "Id 3 position should be 3");
        $this->assertEquals(null, $itemsArr[3]["next"], "Id 3 next item should null");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3
     * - Add 2 with param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 1, 2, 3
     *   - next_film_id values 1->2, 2->3, 3->NULL
     *   - Position values (film_id/position) 1/1, 2/2, 3/3
     *
     * @covers  \RatingSync\Filmlist::addItem()
     * @depends testAddItem
     * @depends testAddItemToDb
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testAddItemToDbDuplicate()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testAddItemToDbDuplicate";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->createToDb();

        // Test
        $success = $list->addItem(2, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 2, 3), $items, "Items should be 1, 2, 3");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(1 => array("pos" => "", "next" => ""), 2 => array("pos" => "", "next" => ""), 3 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[1]["pos"], "Id 1 position should be 1");
        $this->assertEquals(2, $itemsArr[1]["next"], "Id 1 next item should 2");
        $this->assertEquals(2, $itemsArr[2]["pos"], "Id 2 position should be 2");
        $this->assertEquals(3, $itemsArr[2]["next"], "Id 2 next item should 3");
        $this->assertEquals(3, $itemsArr[3]["pos"], "Id 3 position should be 3");
        $this->assertEquals(null, $itemsArr[3]["next"], "Id 3 next item should null");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3
     * - Remove 2 with param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 1, 3
     *   - next_film_id values 1->3, 3->NULL
     *   - Position values (film_id/position) 1/1, 3/2
     *
     * @covers  \RatingSync\Filmlist::removeItem()
     * @depends testRemoveItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testRemoveItemToDb()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveItemToDb";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->createToDb();

        // Test
        $success = $list->removeItem(2, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 3), $items, "Items should be 1, 3");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(1 => array("pos" => "", "next" => ""), 3 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[1]["pos"], "Id 1 position should be 1");
        $this->assertEquals(3, $itemsArr[1]["next"], "Id 1 next item should 3");
        $this->assertEquals(2, $itemsArr[3]["pos"], "Id 3 position should be 2");
        $this->assertEquals(null, $itemsArr[3]["next"], "Id 3 next item should null");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3
     * - Remove 3 with param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 1, 2
     *   - next_film_id values 1->2, 2->NULL
     *   - Position values (film_id/position) 1/1, 2/2
     *
     * @covers  \RatingSync\Filmlist::removeItem()
     * @depends testRemoveItem
     * @depends testRemoveItemToDb
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testRemoveItemToDbAtEnd()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveItemToDbAtEnd";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->createToDb();

        // Test
        $success = $list->removeItem(3, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 2), $items, "Items should be 1, 2");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(1 => array("pos" => "", "next" => ""), 2 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[1]["pos"], "Id 1 position should be 1");
        $this->assertEquals(2, $itemsArr[1]["next"], "Id 1 next item should 2");
        $this->assertEquals(2, $itemsArr[2]["pos"], "Id 2 position should be 2");
        $this->assertEquals(null, $itemsArr[2]["next"], "Id 2 next item should null");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3
     * - Remove 1 with param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 2, 3
     *   - next_film_id values 2->3, 3->NULL
     *   - Position values (film_id/position) 2/1, 3/2
     *
     * @covers  \RatingSync\Filmlist::removeItem()
     * @depends testRemoveItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testRemoveItemToDbAtBegin()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveItemToDbAtBegin";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->createToDb();

        // Test
        $success = $list->removeItem(1, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(2, 3), $items, "Items should be 2, 3");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(2 => array("pos" => "", "next" => ""), 3 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[2]["pos"], "Id 2 position should be 1");
        $this->assertEquals(3, $itemsArr[2]["next"], "Id 2 next item should 3");
        $this->assertEquals(2, $itemsArr[3]["pos"], "Id 3 position should be 2");
        $this->assertEquals(null, $itemsArr[3]["next"], "Id 3 next item should null");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3
     * - Remove 4 with param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 1, 2, 3
     *   - next_film_id values 1->2, 2->3, 3->NULL
     *   - Position values (film_id/position) 1/1, 2/2, 3/3
     *
     * @covers  \RatingSync\Filmlist::removeItem()
     * @depends testRemoveItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testRemoveItemToDbItemDoesNotExist()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveItemToDbItemDoesNotExist";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->createToDb();

        // Test
        $success = $list->removeItem(4, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 2, 3), $items, "Items should be 1, 2, 3");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(1 => array("pos" => "", "next" => ""), 2 => array("pos" => "", "next" => ""), 3 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[1]["pos"], "Id 1 position should be 1");
        $this->assertEquals(2, $itemsArr[1]["next"], "Id 1 next item should 2");
        $this->assertEquals(2, $itemsArr[2]["pos"], "Id 2 position should be 2");
        $this->assertEquals(3, $itemsArr[2]["next"], "Id 2 next item should 3");
        $this->assertEquals(3, $itemsArr[3]["pos"], "Id 3 position should be 3");
        $this->assertEquals(null, $itemsArr[3]["next"], "Id 3 next item should null");
    }

    /**
     * - List does not exist in the db
     * - Remove 2 with param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are empty
     *
     * @covers  \RatingSync\Filmlist::removeItem()
     * @depends testRemoveItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testRemoveItemToDbListDoesNotExist()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveItemToDbListDoesNotExist";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);

        // Test
        $success = $list->removeItem(2, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should have no error(s)");
        $this->assertEquals(array(), $items, "Items should be empty");
    }
    
    /**
     * - List exists in the db with no items
     * - Object has FilmIds 1, 2, 3
     * - Remove 2 with param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are empty
     *
     * @covers  \RatingSync\Filmlist::removeItem()
     * @depends testRemoveItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testRemoveItemToDbEmptyList()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveItemToDbEmptyList";
        $list = new Filmlist($username, $listname);
        $list->createToDb();
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);

        // Test
        $success = $list->removeItem(2, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should have no error(s)");
        $this->assertEquals(array(), $items, "Items should be empty");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 4
     * - Object has FilmIds 1, 2, 3, 4
     * - Remove 3 with param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 1, 2, 4
     *   - next_film_id values 1->2, 2->4, 4->NULL
     *   - Position values (film_id/position) 1/1, 2/2, 4/3
     *
     * @covers  \RatingSync\Filmlist::removeItem()
     * @depends testRemoveItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testRemoveItemToDbItemExistsInObjectButItemDoesNotExistInDb()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveItemToDbExistsInObjectButNotInDb";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(4);
        $list->createToDb();
        $list->addItem(3);

        // Test
        $success = $list->removeItem(3, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should have no error(s)");
        $this->assertEquals(array(1, 2, 4), $items, "Items should be 1, 2, 4");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(1 => array("pos" => "", "next" => ""), 2 => array("pos" => "", "next" => ""), 4 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[1]["pos"], "Id 1 position should be 1");
        $this->assertEquals(2, $itemsArr[1]["next"], "Id 1 next item should 2");
        $this->assertEquals(2, $itemsArr[2]["pos"], "Id 2 position should be 2");
        $this->assertEquals(4, $itemsArr[2]["next"], "Id 2 next item should 4");
        $this->assertEquals(3, $itemsArr[4]["pos"], "Id 4 position should be 3");
        $this->assertEquals(null, $itemsArr[4]["next"], "Id 4 next item should null");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3
     * - Remove 2 with param saveToDb=Default (false)
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 1, 2, 3
     *   - next_film_id values 1->2, 2->3, 3->NULL
     *   - Position values (film_id/position) 1/1, 2/2, 3/3
     *   - Original object items are 1, 3
     *
     * @covers  \RatingSync\Filmlist::removeItem()
     * @depends testRemoveItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testRemoveItemToDbDefault()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveItemToDbDefault";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->createToDb();

        // Test
        $success = $list->removeItem(2);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 2, 3), $items, "Items should be 1, 2, 3");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(1 => array("pos" => "", "next" => ""), 2 => array("pos" => "", "next" => ""), 3 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[1]["pos"], "Id 1 position should be 1");
        $this->assertEquals(2, $itemsArr[1]["next"], "Id 1 next item should 2");
        $this->assertEquals(2, $itemsArr[2]["pos"], "Id 2 position should be 2");
        $this->assertEquals(3, $itemsArr[2]["next"], "Id 2 next item should 3");
        $this->assertEquals(3, $itemsArr[3]["pos"], "Id 3 position should be 3");
        $this->assertEquals(null, $itemsArr[3]["next"], "Id 3 next item should null");
        $objectItems = $list->getItems();
        sort($objectItems, SORT_NUMERIC);
        $this->assertEquals(array(1, 3), $objectItems, "Object items should be 1, 3");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3
     * - Object has FilmIds 1, 3
     * - Remove 2 with param saveToDb=true
     *
     * Expect
     *   - Returns true (no errors)
     *   - After initFromDb() items are 1, 3
     *   - next_film_id values 1->3, 3->NULL
     *   - Position values (film_id/position) 1/1, 3/2
     *
     * @covers  \RatingSync\Filmlist::removeItem()
     * @depends testRemoveItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testRemoveItemToDbItemExistsInDbButNotInObject()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveItemToDbItemExistsInDbButNotInObject";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->createToDb();
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(3);

        // Test
        $success = $list->removeItem(2, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        sort($items, SORT_NUMERIC);
        $this->assertTrue($success, "The function should have no error(s)");
        $this->assertEquals(array(1, 3), $items, "Items should be 1, 3");
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $itemsArr = array(1 => array("pos" => "", "next" => ""), 3 => array("pos" => "", "next" => ""));
        foreach ($result->fetchAll() as $row) {
            $filmId = $row['film_id'];
            $itemsArr[$filmId]["pos"] = $row['position'];
            $itemsArr[$filmId]["next"] = $row['next_film_id'];
        }
        $this->assertEquals(1, $itemsArr[1]["pos"], "Id 1 position should be 1");
        $this->assertEquals(3, $itemsArr[1]["next"], "Id 1 next item should 3");
        $this->assertEquals(2, $itemsArr[3]["pos"], "Id 3 position should be 2");
        $this->assertEquals(null, $itemsArr[3]["next"], "Id 3 next item should null");
    }

    //*RT* testMoveItem

    /**
     * @covers  \RatingSync\Filmlist::count
     * @depends testAddItem
     */
    public function testCount()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(1);
        $list->addItem(2);

        // Verify
        $this->assertEquals(2, $list->count());
    }

    /**
     * Expect
     *   - Film ID's in the original list match the list from the DB (not necessarily sorted)
     *
     * @covers  \RatingSync\Filmlist::getListFromDb
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     */
    public function testGetListFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testGetListFromDb";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->createToDb();

        // Test
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);

        // Sort
        $origFilmIds = $list->getItems();
        $dbFilmIds = $listDb->getItems();
        sort($origFilmIds, SORT_NUMERIC);
        sort($dbFilmIds, SORT_NUMERIC);

        // Verify
        $this->assertEquals($origFilmIds, $dbFilmIds, "Original items should match items from db");
    }

    /**
     * @covers  \RatingSync\Filmlist::removeFromDb
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetListFromDb
     * @depends testCount
     */
    public function testRemoveFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();
    
        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveFromDb";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->createToDb();

        // Test
        $list->removeFromDb();

        // Verify
        $listDb = Filmlist::getListFromDb($username, $listname);
        $this->assertEquals(0, $listDb->count(), "filmlist should have no items");
        $query = "SELECT 1 FROM user_filmlist WHERE user_name='$username' AND listname='$listname'";
        $result = $db->query($query);
        $this->assertEquals(0, $result->rowCount(), "User_filmlist should be gone");
    }

    /**
     * @covers  \RatingSync\Filmlist::removeListFromDb
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testRemoveFromDb
     * @depends testGetListFromDb
     * @depends testCount
     */
    public function testRemoveFilmlistFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();
    
        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testRemoveListFromDb";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->createToDb();

        // Test
        Filmlist::removeListFromDb($username, $listname);

        // Verify
        $listDb = Filmlist::getListFromDb($username, $listname);
        $this->assertEquals(0, $listDb->count(), "filmlist should have no items");
        $query = "SELECT 1 FROM user_filmlist WHERE user_name='$username' AND listname='$listname'";
        $result = $db->query($query);
        $this->assertEquals(0, $result->rowCount(), "User_filmlist should be gone");
    }

    /**
     * @covers  \RatingSync\Filmlist::getUserListsFromDb
     */
    public function testGetUserListsFromDbOnlyDefault()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();
    
        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $query = "DELETE FROM filmlist WHERE user_name='$username'";
        $querySuccess = $db->query($query) !== false;
        $this->assertTrue($querySuccess, "Delete from filmlist");
        $query = "DELETE FROM user_filmlist WHERE user_name='$username' AND listname!='".Constants::LIST_DEFAULT."'";
        $querySuccess = $db->query($query) !== false;
        $this->assertTrue($querySuccess, "Delete from user_filmlist");

        // Set no lists

        // Test
        $lists = Filmlist::getUserListsFromDb($username);

        // Verify
        $this->assertEquals(1, count($lists), "Should be a default list");
    }

    /**
     * @covers  \RatingSync\Filmlist::getUserListsFromDb
     * @depends testCreateToDbSecondList
     * @depends testGetUserListsFromDbOnlyDefault
     */
    public function testGetUserListsFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();
    
        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        /*RT
        $query = "DELETE FROM filmlist WHERE user_name='$username'";
        $querySuccess = $db->query($query) !== false;
        $this->assertTrue($querySuccess);
        *RT*/

        $listname1 = "testUserGetListsFromDb";
        $list1 = new Filmlist($username, $listname1);
        $list1->addItem(1);
        $list1->addItem(2);
        $list1->createToDb();

        $listname2 = "testUserGetListsFromDb_anotherList";
        $list2 = new Filmlist($username, $listname2);
        $list2->addItem(2);
        $list2->addItem(3);
        $list2->addItem(4);
        $list2->createToDb();

        // Test
        $lists = Filmlist::getUserListsFromDb($username);

        // Sort
        $list1_origItems = $list1->getItems();
        $list1_dbItems = $lists[$listname1]->getItems();
        $list2_origItems = $list2->getItems();
        $list2_dbItems = $lists[$listname2]->getItems();
        sort($list1_origItems, SORT_NUMERIC);
        sort($list1_dbItems, SORT_NUMERIC);
        sort($list2_origItems, SORT_NUMERIC);
        sort($list2_dbItems, SORT_NUMERIC);

        // Verify
        $this->assertEquals(3, count($lists), "Should be 3 lists (2 set here plus the default 1");
        $this->assertTrue(array_key_exists($listname1, $lists), "First list ($listname1) should be in the db");
        $this->assertTrue(array_key_exists($listname2, $lists), "Second list ($listname2) should be in the db");
        $this->assertEquals($list1_origItems, $list1_dbItems, "First list should match the db");
        $this->assertEquals($list2_origItems, $list2_dbItems, "Second list should match the db");
    }
    
    /**
     * - The list exists in the db with 2 items
     * - Construct a filmlist object the same listname
     * - Add 1 item matching one of the items in the db
     * - Add 1 item NOT matching the db
     * - Call initFromDb
     *
     * Expect
     *   - List matches the 2 items from the db
     *   - List doesn't have the item that wasn't in the db
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testObjectCanBeConstructed
     * @depends testInitFromDb
     * @depends testAddItem
     * @depends testCreateToDb
     */
    public function testInitFromDbItemsNotFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testInitFromDbItemsNotFromDb";
        $existingList = new Filmlist($username, $listname);
        $existingList->addItem(1);
        $existingList->addItem(2);
        $existingList->createToDb();
        $list = new Filmlist($username, $listname);
        $list->addItem(3);
        $list->setSort(ListSortField::position);
        $list->setSortDirection(SqlSortDirection::ascending);

        // Test
        $list->initFromDb();

        // Verify
        $this->assertEquals($existingList->getItems(), $list->getItems(), "Original items should match items from db");
    }

    /**
     * - The lists in the db with a parent
     * - Construct a list matching the listname
     * - Do not set parentListname
     *
     * Expect
     *   - List object has parentListname matching the db
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testObjectCanBeConstructed
     * @depends testInitFromDb
     * @depends testCreateToDb
     */
    public function testInitFromDbParent()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testInitFromDbParent";
        $parentListname = "testInitFromDbParent_Parent";
        $parent = new Filmlist($username, $parentListname);
        $parent->createToDb();
        $existingList = new Filmlist($username, $listname);
        $existingList->setParentListname($parentListname);
        $existingList->createToDb();
        $list = new Filmlist($username, $listname);

        // Test
        $list->initFromDb();

        // Verify
        $this->assertEquals($parentListname, $list->getParentListname(), "Parent name should match the parent in the db");
    }
    
    public static function setupForFilter()
    {
        $db = getDatabase();
        $errorFree = true;
        $username = Constants::TEST_RATINGSYNC_USERNAME;

        // This assumes the film id in the function here start at 5
        
        $film = new Film();
        $film->setTitle("Feature Comedy 5");
        $film->setContentType(Film::CONTENT_FILM);
        $film->addGenre("Comedy");
        $film->setYear(2016);
        if (! $film->saveToDb()) {
            $errorFree = false;
        }
        
        $film = new Film();
        $film->setTitle("Feature Horror 6");
        $film->setContentType(Film::CONTENT_FILM);
        $film->addGenre("Horror");
        $film->setYear(2016);
        if (! $film->saveToDb()) {
            $errorFree = false;
        }
        
        $film = new Film();
        $film->setTitle("Feature Comedy, Horror 7");
        $film->setContentType(Film::CONTENT_FILM);
        $film->addGenre("Comedy");
        $film->addGenre("Horror");
        $film->setYear(2016);
        if (! $film->saveToDb()) {
            $errorFree = false;
        }
        
        $film = new Film();
        $film->setTitle("TV Series Comedy 8");
        $film->setContentType(Film::CONTENT_TV_SERIES);
        $film->addGenre("Comedy");
        $film->setYear(2016);
        if (! $film->saveToDb()) {
            $errorFree = false;
        }
        
        $film = new Film();
        $film->setTitle("Short Drama 9");
        $film->setContentType(Film::CONTENT_SHORTFILM);
        $film->addGenre("Drama");
        $film->setYear(2016);
        if (! $film->saveToDb()) {
            $errorFree = false;
        }
        
        $listname1 = "filtertests_list1";
        $list = new Filmlist($username, $listname1);
        $list->addItem(5);
        $list->addItem(6);
        $list->addItem(7);
        $list->addItem(8);
        $list->addItem(9);
        if (! $list->createToDb()) {
            $errorFree = false;
        }
        
        $listname2 = "filtertests_list2";
        $list = new Filmlist($username, $listname2);
        $list->addItem(7);
        $list->addItem(8);
        $list->addItem(9);
        if (! $list->createToDb()) {
            $errorFree = false;
        }

        return $errorFree;
    }

    public function setupForSort()
    {
        $db = getDatabase();
        $errorFree = true;
        $username = Constants::TEST_RATINGSYNC_USERNAME;

        // This assumes setupForFilter() created filtertests_list1 with ids 5-9.
        // Order the create_ts will be 5, 7, 9, 8, 6
        
        $query  = "UPDATE filmlist SET create_ts=CURRENT_TIMESTAMP";
        $query .= " WHERE user_name='$username'";
        $query .= "   AND listname='filtertests_list1'";

        $andFilmId = "   AND film_id=7";
        sleep(1);
        if (! $db->query($query . $andFilmId)) {
            $errorFree = false;
        }

        $andFilmId = "   AND film_id=9";
        sleep(1);
        if (! $db->query($query . $andFilmId)) {
            $errorFree = false;
        }

        $andFilmId = "   AND film_id=8";
        sleep(1);
        if (! $db->query($query . $andFilmId)) {
            $errorFree = false;
        }

        $andFilmId = "   AND film_id=6";
        sleep(1);
        if (! $db->query($query . $andFilmId)) {
            $errorFree = false;
        }

        return $errorFree;
    }
    
    /**
     * @depends testSetup
     */
    public function testSetupForFilterAndSort()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->assertTrue(self::setupForFilter(), "setupFilmsForFilter() failed");
        $this->assertTrue(self::setupForSort(), "setupFilmsForSort() failed");
    }

    /**
     * - Use a list in the db that includes 2 Features, 1 TV Series, 1 Short
     * - That filter filters out TV Series or Short
     *
     * Expect
     *   - List has 2 items
     *   - The 2 items are Feature Film
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testSetupForFilterAndSort
     * @depends testObjectCanBeConstructed
     * @depends testInitFromDb
     */
    public function testInitFromDbContentFilter()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "filmlist_testInitFromDbContentFilter";
        $list = new Filmlist($username, $listname);
        $list->addItem(5);
        $list->addItem(6);
        $list->addItem(8);
        $list->addItem(9);
        $success = $list->createToDb();
        $this->assertTrue($success, "Failure in Set Up phase of this function");

        // Test
        $contentFilter = array();
        $contentFilter[Film::CONTENT_TV_SERIES] = false;
        $contentFilter[Film::CONTENT_SHORTFILM] = false;
        $list->setContentFilter($contentFilter);
        $list->initFromDb();

        // Verify
        $items = $list->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(5, 6);
        $this->assertEquals(2, count($items), "List should have 2 items");
        $this->assertEquals($itemsExpected, $items, "List items should 5, 6 (Feature films in the list)");
    }

    /**
     * - Use a list in the db that includes 2 Comedies, 1 Drama, 1 Comedy/Horror, 1 Horror
     *     - Try 1: with a filter that retrieves only films with Comedy
     *     - Try 2: with a filter that retrieves only films with Comedy OR Horror
     *     - Try 3: with a filter that retrieves only films with Comedy AND Horror
     *
     * Expect
     *   - Try 1: List has 3 items matching Comedy
     *   - Try 2: List has 4 items matching Comedy (2), Comedy/Horror (1), Horror (1)
     *   - Try 3: List has 1 item matching Comedy/Horror
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testSetupForFilterAndSort
     * @depends testObjectCanBeConstructed
     * @depends testInitFromDb
     */
    public function testInitFromDbGenreFilter()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "filmlist_testInitFromDbGenreFilter";
        $list = new Filmlist($username, $listname);
        $list->addItem(5);
        $list->addItem(6);
        $list->addItem(7);
        $list->addItem(8);
        $list->addItem(9);
        $success = $list->createToDb();
        $this->assertTrue($success, "Failure in Set Up phase of this function");

        // Test 1
        $genreFilter = array("Comedy");
        $list->setGenreFilter($genreFilter);
        $list->initFromDb();

        // Verify 1
        $items = $list->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(5, 7, 8);
        $this->assertEquals(3, count($items), "List should have 3 items");
        $this->assertEquals($itemsExpected, $items, "List items should 5, 7, 8 (Comedy films in the list)");

        // Test 2
        $genreFilter = array("Comedy", "Horror");
        $list->setGenreFilter($genreFilter);
        $list->initFromDb();

        // Verify 2
        $items = $list->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(5, 6, 7, 8);
        $this->assertEquals(4, count($items), "List should have 4 items");
        $this->assertEquals($itemsExpected, $items, "List items should 5, 6, 7, 8 (Comedy or Horror films in the list)");

        // Test 3
        $genreFilter = array("Comedy", "Horror");
        $list->setGenreFilter($genreFilter);
        $list->setGenreFilterMatchAny(false);
        $list->initFromDb();

        // Verify 3
        $items = $list->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(7);
        $this->assertEquals(1, count($items), "List should have 1 item");
        $this->assertEquals($itemsExpected, $items, "List item should '7' (Comedy/Horror films in the list)");
    }

    /**
     * - Lists in the db
     *     - list1 includes filmIds: 5, 6, 7
     *     - list2 includes filmIds: 6, 7, 8
     *     - list3 includes filmIds: 7, 8, 9
     *     - list4 includes filmIds: 5, 9
     *     - list5 includes filmIds: 8, 9
     *     - list6 includes filmIds: none
     * - Try different combinations
     *     - Try 1: Use list1 with list2 (with one other list)
     *     - Try 2: Use list1 with list3 and list4 (with two other lists)
     *     - Try 3: Use list1 with list5 (with no intersection)
     *     - Try 4: Use list1 with list6 (with an empty list)
     *
     * Expect
     *   - Try 1: Returns 6, 7
     *   - Try 2: Returns 5, 7
     *   - Try 3: Returns none
     *   - Try 4: Returns none
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testSetupForFilterAndSort
     * @depends testObjectCanBeConstructed
     * @depends testInitFromDb
     */
    public function testInitFromDbListFilter()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname1 = "filmlist_testInitFromDbListFilter1";
        $list1 = new Filmlist($username, $listname1);
        $list1->addItem(5);
        $list1->addItem(6);
        $list1->addItem(7);
        $success = $list1->createToDb();
        $this->assertTrue($success, "Failure in Set Up phase of this function");
        $listname2 = "filmlist_testInitFromDbListFilter2";
        $list2 = new Filmlist($username, $listname2);
        $list2->addItem(6);
        $list2->addItem(7);
        $list2->addItem(8);
        $success = $list2->createToDb();
        $this->assertTrue($success, "Failure in Set Up phase of this function");
        $listname3 = "filmlist_testInitFromDbListFilter3";
        $list3 = new Filmlist($username, $listname3);
        $list3->addItem(7);
        $list3->addItem(8);
        $list3->addItem(9);
        $success = $list3->createToDb();
        $this->assertTrue($success, "Failure in Set Up phase of this function");
        $listname4 = "filmlist_testInitFromDbListFilter4";
        $list4 = new Filmlist($username, $listname4);
        $list4->addItem(5);
        $list4->addItem(9);
        $success = $list4->createToDb();
        $this->assertTrue($success, "Failure in Set Up phase of this function");
        $listname5 = "filmlist_testInitFromDbListFilter5";
        $list5 = new Filmlist($username, $listname5);
        $list5->addItem(8);
        $list5->addItem(9);
        $success = $list5->createToDb();
        $this->assertTrue($success, "Failure in Set Up phase of this function");
        $listname6 = "filmlist_testInitFromDbListFilter6";
        $list6 = new Filmlist($username, $listname6);
        $success = $list6->createToDb();
        $this->assertTrue($success, "Failure in Set Up phase of this function");

        // Test 1
        $listFilter = array($listname2);
        $list1->setListFilter($listFilter);
        $list1->initFromDb();

        // Verify 1
        $items = $list1->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(6, 7);
        $this->assertEquals($itemsExpected, $items, "List items should 6, 7");

        // Test 2
        $listFilter = array($listname3, $listname4);
        $list1->setListFilter($listFilter);
        $list1->initFromDb();

        // Verify 2
        $items = $list1->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(5, 7);
        $this->assertEquals($itemsExpected, $items, "List items should 5, 7");

        // Test 3
        $listFilter = array($listname5);
        $list1->setListFilter($listFilter);
        $list1->initFromDb();

        // Verify 3
        $items = $list1->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array();
        $this->assertEquals($itemsExpected, $items, "List items should empty");

        // Test 4
        $listFilter = array($listname6);
        $list1->setListFilter($listFilter);
        $list1->initFromDb();

        // Verify 4
        $items = $list1->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array();
        $this->assertEquals($itemsExpected, $items, "List items should be empty");
    }

    /**
     * - Films in the list in the db (Id, Content, Genre)
     *     - 5  Feature     Comedy
     *     - 6  Feature     Horror
     *     - 7  Feature     Comedy/Horror
     *     - 8  TV Series   Comedy
     *     - 9  Short       Drama
     * - Test different combinations
     *     - Test 1: Basic- Filter out Feature (8, 9), Filter in Comedy (5, 7, 8)
     *     - Test 2: Any with genre- Filter out TV Series (5, 6, 7, 9), Filter by Comedy OR Horror (5, 6, 7, 8)
     *     - Test 3: All with genre- Filter out TV Series (5, 6, 7, 9), Filter by Comedy AND Horror (7)
     *     - Test 4: All genre with no match- Filter out Feature (8, 9), Filter by Comedy AND Horror (7)
     *
     * Expect
     *   - Test 1: Returns 8
     *   - Test 2: Returns 5, 6, 7
     *   - Test 3: Returns 7
     *   - Test 4: Returns none
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testSetupForFilterAndSort
     * @depends testObjectCanBeConstructed
     * @depends testInitFromDb
     * @depends testInitFromDbContentFilter
     * @depends testInitFromDbGenreFilter
     */
    public function testInitFromDbContentGenreFilter()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "filtertests_list1";
        $list = new Filmlist($username, $listname);

        // Test 1
        $list->setContentFilter(array(Film::CONTENT_FILM => false));
        $list->setGenreFilter(array("Comedy"));
        $list->initFromDb();

        // Verify 1
        $items = $list->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(8);
        $this->assertEquals($itemsExpected, $items, "List item should be 8");

        // Test 2
        $list->setContentFilter(array(Film::CONTENT_TV_SERIES => false));
        $list->setGenreFilter(array("Comedy", "Horror"));
        $list->setGenreFilterMatchAny(true);
        $list->initFromDb();

        // Verify 2
        $items = $list->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(5, 6, 7);
        $this->assertEquals($itemsExpected, $items, "List items should be 5, 6, 7");

        // Test 3
        $list->setContentFilter(array(Film::CONTENT_TV_SERIES => false));
        $list->setGenreFilter(array("Comedy", "Horror"));
        $list->setGenreFilterMatchAny(false);
        $list->initFromDb();

        // Verify 3
        $items = $list->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(7);
        $this->assertEquals($itemsExpected, $items, "List item should be 7");

        // Test 4
        $list->setContentFilter(array(Film::CONTENT_FILM => false));
        $list->setGenreFilter(array("Comedy", "Horror"));
        $list->setGenreFilterMatchAny(false);
        $list->initFromDb();

        // Verify 4
        $items = $list->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array();
        $this->assertEquals($itemsExpected, $items, "List item should be empty");
    }

    /**
     * - Films in the db (Id, Content, Genre)
     *     - 5  Feature     Comedy
     *     - 6  Feature     Horror
     *     - 7  Feature     Comedy/Horror
     *     - 8  TV Series   Comedy
     *     - 9  Short       Drama
     * - Lists in the db
     *     - list1 includes filmIds: 5, 6, 7, 8, 9
     *     - list2 includes filmIds: 7, 8, 9
     * - Test different combinations
     *     - Test 1: Use list1. Filter in list2 (7,8,9). Filter out TV Series (5,6,7,9).
     *
     * Expect
     *   - Test 1: Returns 7, 9
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testSetupForFilterAndSort
     * @depends testObjectCanBeConstructed
     * @depends testInitFromDb
     * @depends testInitFromDbContentFilter
     * @depends testInitFromDbListFilter
     */
    public function testInitFromDbContentListFilter()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $list1 = new Filmlist($username, "filtertests_list1");
        $listname2 = "filtertests_list2";
        $list2 = new Filmlist($username, $listname2);

        // Test
        $list1->setContentFilter(array(Film::CONTENT_TV_SERIES => false));
        $list1->setListFilter(array($listname2));
        $list1->initFromDb();

        // Verify
        $items = $list1->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(7, 9);
        $this->assertEquals($itemsExpected, $items, "List items should be 7, 9");
    }

    /**
     * - Films in the db (Id, Content, Genre)
     *     - 5  Feature     Comedy
     *     - 6  Feature     Horror
     *     - 7  Feature     Comedy/Horror
     *     - 8  TV Series   Comedy
     *     - 9  Short       Drama
     * - Lists in the db
     *     - list1 includes filmIds: 5, 6, 7, 8, 9
     *     - list2 includes filmIds: 7, 8, 9
     * - Test different combinations
     *     - Test 1: Use list1. Filter in list2 (7,8,9). Filter in Comedy (7,8)
     *
     * Expect
     *   - Test 1: Returns 7, 8
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testSetupForFilterAndSort
     * @depends testObjectCanBeConstructed
     * @depends testInitFromDb
     * @depends testInitFromDbGenreFilter
     * @depends testInitFromDbListFilter
     */
    public function testInitFromDbGenreListFilter()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $list1 = new Filmlist($username, "filtertests_list1");
        $listname2 = "filtertests_list2";
        $list2 = new Filmlist($username, $listname2);

        // Test
        $list1->setListFilter(array($listname2));
        $list1->setGenreFilter(array("Comedy"));
        $list1->initFromDb();

        // Verify
        $items = $list1->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(7, 8);
        $this->assertEquals($itemsExpected, $items, "List items should be 7, 8");
    }

    /**
     * - Films in the db (Id, Content, Genre)
     *     - 5  Feature     Comedy
     *     - 6  Feature     Horror
     *     - 7  Feature     Comedy/Horror
     *     - 8  TV Series   Comedy
     *     - 9  Short       Drama
     * - Lists in the db
     *     - list1 includes filmIds: 5, 6, 7, 8, 9
     *     - list2 includes filmIds: 7, 8, 9
     * - Test different combinations
     *     - Test 1: Use list1. Filter in list2 (7,8,9). Filter out TV Series (5,6,7,9). Filter in Comedy (5,7,8).
     *
     * Expect
     *   - Test 1: Returns 7
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testSetupForFilterAndSort
     * @depends testObjectCanBeConstructed
     * @depends testInitFromDb
     * @depends testInitFromDbContentGenreFilter
     * @depends testInitFromDbContentListFilter
     * @depends testInitFromDbGenreListFilter
     */
    public function testInitFromDb3WayComboFilter()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $list1 = new Filmlist($username, "filtertests_list1");
        $listname2 = "filtertests_list2";

        // Test
        $list1->setListFilter(array($listname2));
        $list1->setContentFilter(array(Film::CONTENT_TV_SERIES => false));
        $list1->setGenreFilter(array("Comedy"));
        $list1->initFromDb();

        // Verify
        $items = $list1->getItems();
        sort($items, SORT_NUMERIC);
        $itemsExpected = array(7);
        $this->assertEquals($itemsExpected, $items, "List item should be 7");
    }

    /**
     * - list1 items in order by position ASC: 5, 6, 7, 8, 9
     * - list1 items in order by create_ts ASC: 5, 7, 9, 8, 6
     * - Test 1: Sort by position ascending
     * - Test 2: Sort by position descending
     * - Test 3: Sort by modified ascending
     * - Test 4: Sort by modified descending
     *
     * Expect
     *   - Test 1: Return 5, 6, 7, 8, 9
     *   - Test 2: Return 9, 8, 7, 6, 5
     *   - Test 3: Return 5, 7, 9, 8, 6
     *   - Test 4: Return 6, 8, 9, 7, 5
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testObjectCanBeConstructed
     * @depends testSetupForFilterAndSort
     * @depends testInitFromDb
     */
    public function testInitFromDbSort()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $list1 = new Filmlist($username, "filtertests_list1");

        // Test 1
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->initFromDb();

        // Verify 1
        $items = $list1->getItems();
        $itemsExpected = array(5, 6, 7, 8, 9);
        $this->assertEquals($itemsExpected, $items, "List item order should be 5, 6, 7, 8, 9");

        // Test 2
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->initFromDb();

        // Verify 2
        $items = $list1->getItems();
        $itemsExpected = array(9, 8, 7, 6, 5);
        $this->assertEquals($itemsExpected, $items, "List item order should be 9, 8, 7, 6, 5");

        // Test 3
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->initFromDb();

        // Verify 3
        $items = $list1->getItems();
        $itemsExpected = array(5, 7, 9, 8, 6);
        $this->assertEquals($itemsExpected, $items, "List item order should be 5, 7, 9, 8, 6");

        // Test 4
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->initFromDb();

        // Verify 4
        $items = $list1->getItems();
        $itemsExpected = array(6, 8, 9, 7, 5);
        $this->assertEquals($itemsExpected, $items, "List item order should be 6, 8, 9, 7, 5");
    }

    /**
     * - Films in the db (Id, Content, Genre)
     *     - 5  Feature     Comedy
     *     - 6  Feature     Horror
     *     - 7  Feature     Comedy/Horror
     *     - 8  TV Series   Comedy
     *     - 9  Short       Drama
     * - Lists in the db
     *     - list1 includes filmIds: 5, 6, 7, 8, 9
     *     - list2 includes filmIds: 7, 8, 9
     *     - list3 includes filmIds: 6, 7, 8, 9
     * - list1 items in order by position ASC: 5, 6, 7, 8, 9
     * - list1 items in order by create_ts ASC: 6, 8, 9, 7, 5
     *
     * - Test 1: Filter out Content (TV Series, Short), Position/Asc
     *     - Return 5, 6, 7
     * - Test 2: Filter out Content (TV Series, Short), Position/Desc
     *     - Return 7, 6, 5
     * - Test 3: Filter out Content (TV Series, Short), Modified/Asc
     *     - Return 5, 7, 6
     * - Test 4: Filter out Content (TV Series, Short), Modified/Desc
     *     - Return 6, 7, 5
     * - Test 5: Filter in Genre (Comedy OR Drama), Position/Asc
     *     - Return 5, 7, 8, 9
     * - Test 6: Filter in Genre (Comedy OR Drama), Position/Desc
     *     - Return 9, 8, 7, 5
     * - Test 7: Filter in Genre (Comedy OR Drama), Modified/Asc
     *     - Return 5, 7, 9, 8
     * - Test 8: Filter in Genre (Comedy OR Drama), Modified/Desc
     *     - Return 8, 9, 7, 5
     * - Test 9: Filter in list2, Position/Asc
     *     - Return 7, 8, 9
     * - Test 10: Filter in list2, Position/Desc
     *     - Return 9, 8, 7
     * - Test 11: Filter in list2, Modified/Asc
     *     - Return 7, 9, 8
     * - Test 12: Filter in list2, Modified/Desc
     *     - Return 8, 9, 7
     * - Test 13: Filter out Content (Feature), Filter in list2, Position/Asc
     *     - Return 8, 9
     * - Test 14: Filter out Content (Feature), Filter in list2, Position/Desc
     *     - Return 9, 8
     * - Test 15: Filter out Content (Feature), Filter in list2, Modified/Asc
     *     - Return 9, 8
     * - Test 16: Filter out Content (Feature), Filter in list2, Modified/Desc
     *     - Return 8, 9
     * - Test 17: Filter in Genre (Comedy OR Horror), Filter in list3, Position/Asc
     *     - Return 6, 7, 8
     * - Test 18: Filter in Genre (Comedy OR Horror), Filter in list3, Position/Desc
     *     - Return 8, 7, 6
     * - Test 19: Filter in Genre (Comedy OR Horror), Filter in list3, Modified/Asc
     *     - Return 7, 8, 6
     * - Test 20: Filter in Genre (Comedy OR Horror), Filter in list3, Modified/Desc
     *     - Return 6, 8, 7
     * - Test 21: Filter out Content (TV Series), Filter in Genre (Comedy OR Horror), Position/Asc
     *     - Return 5, 6, 7
     * - Test 22: Filter out Content (TV Series), Filter in Genre (Comedy OR Horror), Position/Desc
     *     - Return 7, 6, 5
     * - Test 23: Filter out Content (TV Series), Filter in Genre (Comedy OR Horror), Modified/Asc
     *     - Return 5, 7, 6
     * - Test 24: Filter out Content (TV Series), Filter in Genre (Comedy OR Horror), Filter in list3, Modified/Desc
     *     - Return 6, 7, 5
     * - Test 25: Filter out Content (Feature), Filter in Genre (Comedy OR Drama), Filter in list3, Position/Asc
     *     - Return 8, 9
     * - Test 26: Filter out Content (Feature), Filter in Genre (Comedy OR Drama), Filter in list3, Position/Desc
     *     - Return 9, 8
     * - Test 27: Filter out Content (Feature), Filter in Genre (Comedy OR Drama), Filter in list3, Modified/Asc
     *     - Return 9, 8
     * - Test 28: Filter out Content (Feature), Filter in Genre (Comedy OR Drama), Filter in list3, Filter in list3, Modified/Desc
     *     - Return 8, 9
     *
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testObjectCanBeConstructed
     * @depends testSetupForFilterAndSort
     * @depends testInitFromDb
     * @depends testInitFromDbSort
     * @depends testInitFromDbContentFilter
     * @depends testInitFromDbGenreFilter
     * @depends testInitFromDbListFilter
     * @depends testInitFromDbContentListFilter
     * @depends testInitFromDbGenreListFilter
     * @depends testInitFromDbContentGenreFilter
     * @depends testInitFromDb3WayComboFilter
     */
    public function testInitFromDbSortWithFilters()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $list1 = new Filmlist($username, "filtertests_list1");
        $listname2 = "filtertests_list2";
        $listname3 = "filtertests_list3";
        $list3 = new Filmlist($username, $listname3);
        $list3->addItem(6);
        $list3->addItem(7);
        $list3->addItem(8);
        $list3->addItem(9);
        $list3->createToDb();

        // Test 1
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array(Film::CONTENT_TV_SERIES => false, Film::CONTENT_SHORTFILM => false));
        $list1->setGenreFilter(array());
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 1
        $items = $list1->getItems();
        $itemsExpected = array(5, 6, 7);
        $this->assertEquals($itemsExpected, $items, "List item order should be 5, 6, 7");

        // Test 2
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array(Film::CONTENT_TV_SERIES => false, Film::CONTENT_SHORTFILM => false));
        $list1->setGenreFilter(array());
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 2
        $items = $list1->getItems();
        $itemsExpected = array(7, 6, 5);
        $this->assertEquals($itemsExpected, $items, "List item order should be 7, 6, 5");

        // Test 3
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array(Film::CONTENT_TV_SERIES => false, Film::CONTENT_SHORTFILM => false));
        $list1->setGenreFilter(array());
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 3
        $items = $list1->getItems();
        $itemsExpected = array(5, 7, 6);
        $this->assertEquals($itemsExpected, $items, "List item order should be 5, 7, 6");

        // Test 4
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array(Film::CONTENT_TV_SERIES => false, Film::CONTENT_SHORTFILM => false));
        $list1->setGenreFilter(array());
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 4
        $items = $list1->getItems();
        $itemsExpected = array(6, 7, 5);
        $this->assertEquals($itemsExpected, $items, "List item order should be 6, 7, 5");
        
        // Test 5
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array("Comedy", "Drama"));
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 5
        $items = $list1->getItems();
        $itemsExpected = array(5, 7, 8, 9);
        $this->assertEquals($itemsExpected, $items, "List item order should be 5, 7, 8, 9");

        // Test 6
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array("Comedy", "Drama"));
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 6
        $items = $list1->getItems();
        $itemsExpected = array(9, 8, 7, 5);
        $this->assertEquals($itemsExpected, $items, "List item order should be 9, 8, 7, 5");

        // Test 7
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array("Comedy", "Drama"));
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 7
        $items = $list1->getItems();
        $itemsExpected = array(5, 7, 9, 8);
        $this->assertEquals($itemsExpected, $items, "List item order should be 5, 7, 9, 8");

        // Test 8
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array("Comedy", "Drama"));
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 8
        $items = $list1->getItems();
        $itemsExpected = array(8, 9, 7, 5);
        $this->assertEquals($itemsExpected, $items, "List item order should be 8, 9, 7, 5");

        // Test 9
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array());
        $list1->setListFilter(array($listname2));
        $list1->initFromDb();
        
        // Verify 9
        $items = $list1->getItems();
        $itemsExpected = array(7, 8, 9);
        $this->assertEquals($itemsExpected, $items, "List item order should be 7, 8, 9");

        // Test 10
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array());
        $list1->setListFilter(array($listname2));
        $list1->initFromDb();

        // Verify 10
        $items = $list1->getItems();
        $itemsExpected = array(9, 8, 7);
        $this->assertEquals($itemsExpected, $items, "List item order should be 9, 8, 7");

        // Test 11
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array());
        $list1->setListFilter(array($listname2));
        $list1->initFromDb();

        // Verify 11
        $items = $list1->getItems();
        $itemsExpected = array(7, 9, 8);
        $this->assertEquals($itemsExpected, $items, "List item order should be 7, 9, 8");

        // Test 12
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array());
        $list1->setListFilter(array($listname2));
        $list1->initFromDb();

        // Verify 12
        $items = $list1->getItems();
        $itemsExpected = array(8, 9, 7);
        $this->assertEquals($itemsExpected, $items, "List item order should be 8, 9, 7");

        // Test 13
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array(Film::CONTENT_FILM => false));
        $list1->setGenreFilter(array());
        $list1->setListFilter(array($listname2));
        $list1->initFromDb();
        
        // Verify 13
        $items = $list1->getItems();
        $itemsExpected = array(8, 9);
        $this->assertEquals($itemsExpected, $items, "List item order should be 8, 9");

        // Test 14
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array(Film::CONTENT_FILM => false));
        $list1->setGenreFilter(array());
        $list1->setListFilter(array($listname2));
        $list1->initFromDb();

        // Verify 14
        $items = $list1->getItems();
        $itemsExpected = array(9, 8);
        $this->assertEquals($itemsExpected, $items, "List item order should be 9, 8");

        // Test 15
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array(Film::CONTENT_FILM => false));
        $list1->setGenreFilter(array());
        $list1->setListFilter(array($listname2));
        $list1->initFromDb();

        // Verify 15
        $items = $list1->getItems();
        $itemsExpected = array(9, 8);
        $this->assertEquals($itemsExpected, $items, "List item order should be 9, 8");

        // Test 16
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array(Film::CONTENT_FILM => false));
        $list1->setGenreFilter(array());
        $list1->setListFilter(array($listname2));
        $list1->initFromDb();

        // Verify 16
        $items = $list1->getItems();
        $itemsExpected = array(8, 9);
        $this->assertEquals($itemsExpected, $items, "List item order should be 8, 9");
        
        // Test 17
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array("Comedy", "Horror"));
        $list1->setListFilter(array($listname3));
        $list1->initFromDb();

        // Verify 17
        $items = $list1->getItems();
        $itemsExpected = array(6, 7, 8);
        $this->assertEquals($itemsExpected, $items, "List item order should be 6, 7, 8");

        // Test 18
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array("Comedy", "Horror"));
        $list1->setListFilter(array($listname3));
        $list1->initFromDb();

        // Verify 18
        $items = $list1->getItems();
        $itemsExpected = array(8, 7, 6);
        $this->assertEquals($itemsExpected, $items, "List item order should be 8, 7, 6");

        // Test 19
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array("Comedy", "Horror"));
        $list1->setListFilter(array($listname3));
        $list1->initFromDb();

        // Verify 19
        $items = $list1->getItems();
        $itemsExpected = array(7, 8, 6);
        $this->assertEquals($itemsExpected, $items, "List item order should be 7, 8, 6");

        // Test 20
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array());
        $list1->setGenreFilter(array("Comedy", "Horror"));
        $list1->setListFilter(array($listname3));
        $list1->initFromDb();

        // Verify 20
        $items = $list1->getItems();
        $itemsExpected = array(6, 8, 7);
        $this->assertEquals($itemsExpected, $items, "List item order should be 6, 8, 7");

        // Test 21
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array(Film::CONTENT_TV_SERIES => false));
        $list1->setGenreFilter(array("Comedy", "Horror"));
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 21
        $items = $list1->getItems();
        $itemsExpected = array(5, 6, 7);
        $this->assertEquals($itemsExpected, $items, "List item order should be 5, 6, 7");

        // Test 22
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array(Film::CONTENT_TV_SERIES => false));
        $list1->setGenreFilter(array("Comedy", "Horror"));
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 22
        $items = $list1->getItems();
        $itemsExpected = array(7, 6, 5);
        $this->assertEquals($itemsExpected, $items, "List item order should be 7, 6, 5");

        // Test 23
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array(Film::CONTENT_TV_SERIES => false));
        $list1->setGenreFilter(array("Comedy", "Horror"));
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 23
        $items = $list1->getItems();
        $itemsExpected = array(5, 7, 6);
        $this->assertEquals($itemsExpected, $items, "List item order should be 5, 7, 6");

        // Test 24
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array(Film::CONTENT_TV_SERIES => false));
        $list1->setGenreFilter(array("Comedy", "Horror"));
        $list1->setListFilter(array());
        $list1->initFromDb();

        // Verify 24
        $items = $list1->getItems();
        $itemsExpected = array(6, 7, 5);
        $this->assertEquals($itemsExpected, $items, "List item order should be 6, 7, 5");
        
        // Test 25
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array(Film::CONTENT_FILM => false));
        $list1->setGenreFilter(array("Comedy", "Drama"));
        $list1->setListFilter(array($listname3));
        $list1->initFromDb();

        // Verify 25
        $items = $list1->getItems();
        $itemsExpected = array(8, 9);
        $this->assertEquals($itemsExpected, $items, "List item order should be 8, 9");

        // Test 26
        $list1->setSort(ListSortField::position);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array(Film::CONTENT_FILM => false));
        $list1->setGenreFilter(array("Comedy", "Drama"));
        $list1->setListFilter(array($listname3));
        $list1->initFromDb();

        // Verify 26
        $items = $list1->getItems();
        $itemsExpected = array(9, 8);
        $this->assertEquals($itemsExpected, $items, "List item order should be 9, 8");

        // Test 27
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::ascending);
        $list1->setContentFilter(array(Film::CONTENT_FILM => false));
        $list1->setGenreFilter(array("Comedy", "Drama"));
        $list1->setListFilter(array($listname3));
        $list1->initFromDb();

        // Verify 27
        $items = $list1->getItems();
        $itemsExpected = array(9, 8);
        $this->assertEquals($itemsExpected, $items, "List item order should be 9, 8");

        // Test 28
        $list1->setSort(ListSortField::modified);
        $list1->setSortDirection(SqlSortDirection::descending);
        $list1->setContentFilter(array(Film::CONTENT_FILM => false));
        $list1->setGenreFilter(array("Comedy", "Drama"));
        $list1->setListFilter(array($listname3));
        $list1->initFromDb();

        // Verify 28
        $items = $list1->getItems();
        $itemsExpected = array(8, 9);
        $this->assertEquals($itemsExpected, $items, "List item order should be 8, 9");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 4 to before filmId 2
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 1, 4, 2, 3, 5
     *
     * @covers  \RatingSync\Filmlist::moveItem()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetItems
     */
    public function testMoveItem()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItem";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(4, 2);

        // Verify
        $items = $list->getItems();
        $this->assertTrue($success, "The function should have no errors");
        $this->assertEquals(array(1, 4, 2, 3, 5), $items, "Items should be 1, 4, 2, 3, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 5 to before filmId 2
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 1, 5, 2, 3, 4
     *
     * @covers  \RatingSync\Filmlist::moveItem()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetItems
     */
    public function testMoveItemFromEnd()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemFromEnd";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(5, 2);

        // Verify
        $items = $list->getItems();
        $this->assertTrue($success, "The function should have no errors");
        $this->assertEquals(array(1, 5, 2, 3, 4), $items, "Items should be 1, 5, 2, 3, 4");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 1 to before filmId 3
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 2, 1, 3, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItem()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetItems
     */
    public function testMoveItemFromFirst()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemFromFirst";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(1, 3);

        // Verify
        $items = $list->getItems();
        $this->assertTrue($success, "The function should have no errors");
        $this->assertEquals(array(2, 1, 3, 4, 5), $items, "Items should be 2, 1, 3, 4, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 3 to before filmId -1 (the end)
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 1, 2, 4, 5, 3
     *
     * @covers  \RatingSync\Filmlist::moveItem()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetItems
     */
    public function testMoveItemToEnd()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemToEnd";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(3, -1);

        // Verify
        $items = $list->getItems();
        $this->assertTrue($success, "The function should have no errors");
        $this->assertEquals(array(1, 2, 4, 5, 3), $items, "Items should be 1, 2, 4, 5, 3");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 3 to before filmId 1
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 3, 1, 2, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItem()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetItems
     */
    public function testMoveItemToFirst()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemToFirst";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(3, 1);

        // Verify
        $items = $list->getItems();
        $this->assertTrue($success, "The function should have no errors");
        $this->assertEquals(array(3, 1, 2, 4, 5), $items, "Items should be 3, 1, 2, 4, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 3 to before filmId 3
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 1, 2, 3, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItem()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetItems
     */
    public function testMoveItemNoChange()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemNoChange";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(3, 3);

        // Verify
        $items = $list->getItems();
        $this->assertTrue($success, "The function should have no errors");
        $this->assertEquals(array(1, 2, 3, 4, 5), $items, "Items should be 1, 2, 3, 4, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 6 to before filmId 3
     *
     * Expect
     *   - Returns false (errors)
     *   - Items are 1, 2, 3, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItem()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetItems
     */
    public function testMoveItemDoesNotExist()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemDoesNotExist";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(6, 3);

        // Verify
        $items = $list->getItems();
        $this->assertFalse($success, "The function should have error(s)");
        $this->assertEquals(array(1, 2, 3, 4, 5), $items, "Items should be 1, 2, 3, 4, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 3 to before filmId 6
     *
     * Expect
     *   - Returns false (errors)
     *   - Items are 1, 2, 3, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItem()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testGetItems
     */
    public function testMoveItemNextItemDoesNotExist()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemNextItemDoesNotExist";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(3, 6);

        // Verify
        $items = $list->getItems();
        $this->assertFalse($success, "The function should have error(s)");
        $this->assertEquals(array(1, 2, 3, 4, 5), $items, "Items should be 1, 2, 3, 4, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 4 to before filmId 2
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 1, 4, 2, 3, 5
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDb()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDb";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();

        // Test
        $success = $list->moveItem(4, 2, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 4, 2, 3, 5), $items, "Items should be 1, 4, 2, 3, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 5 to before filmId 2
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 1, 5, 2, 3, 4
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbFromEnd()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbFromEnd";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();

        // Test
        $success = $list->moveItem(5, 2, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 5, 2, 3, 4), $items, "Items should be 1, 5, 2, 3, 4");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 1 to before filmId 4
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 2, 3, 1, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbFromFirst()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbFromFirst";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();

        // Test
        $success = $list->moveItem(1, 4, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(2, 3, 1, 4, 5), $items, "Items should be 2, 3, 1, 4, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 3 to before filmId -1 (after the end)
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 1, 2, 4, 5, 3
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbToEnd()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbToEnd";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();

        // Test
        $success = $list->moveItem(3, -1, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 2, 4, 5, 3), $items, "Items should be 1, 2, 4, 5, 3");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 3 to before filmId 1
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 3, 1, 2, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbToFirst()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbToFirst";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();

        // Test
        $success = $list->moveItem(3, 1, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(3, 1, 2, 4, 5), $items, "Items should be 3, 1, 2, 4, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Test 1: Move filmId 3 to before filmId 4
     * - Test 2: Move filmId 3 to before filmId 3
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items are 1, 2, 3, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbNoChange()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbNoChange";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();

        // Test 1
        $success = $list->moveItem(3, 4, true);

        // Verify 1
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 2, 3, 4, 5), $items, "Items should be 1, 2, 3, 4, 5");

        // Test 2
        $success = $list->moveItem(3, 3, true);

        // Verify 2
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertTrue($success, "The function should be error free");
        $this->assertEquals(array(1, 2, 3, 4, 5), $items, "Items should be 1, 2, 3, 4, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 6 to before filmId 2
     *
     * Expect
     *   - Returns false (errors)
     *   - Items are 1, 2, 3, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbItemDoesNotExist()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbItemDoesNotExist";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();

        // Test
        $success = $list->moveItem(6, 2, true);

        // Verify
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertFalse($success, "The function should be have error(s)");
        $this->assertEquals(array(1, 2, 3, 4, 5), $items, "Items should be 1, 2, 3, 4, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - Move filmId 4 to before filmId 6
     *
     * Expect
     *   - Returns false (errors)
     *   - Items are 1, 2, 3, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbNextDoesNotExist()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbNextDoesNotExist";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();

        // Test 2
        $success = $list->moveItem(4, 6, true);

        // Verify 2
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertFalse($success, "The function should be have error(s)");
        $this->assertEquals(array(1, 2, 3, 4, 5), $items, "Items should be 1, 2, 3, 4, 5");
    }
    
    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4
     * - List object with the same name: 1, 2, 3, 4, 5
     * - Move filmId 5 to before filmId 2
     *
     * Expect
     *   - Returns false (errors)
     *   - Items from db: 1, 2, 3, 4
     *   - Items for original object: 1, 5, 2, 3, 4
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbItemExistsInObjectNotInDb()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbItemExistsInObjectNotInDb";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->createToDb();
        $list->addItem(5);

        // Test
        $success = $list->moveItem(5, 2, true);

        // Verify
        $this->assertEquals(array(1, 5, 2, 3, 4), $list->getItems(), "Items should be 1, 5, 2, 3, 4");
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertFalse($success, "The function should be have error(s)");
        $this->assertEquals(array(1, 2, 3, 4), $items, "Items should be 1, 2, 3, 4");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - List object with the same name: 1, 2, 3, 4
     * - Move filmId 5 to before filmId 2
     *
     * Expect
     *   - Returns false (errors)
     *   - Items from db: 1, 5, 2, 3, 4
     *   - Items for original object: 1, 2, 3, 4
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbExistsInDbNotInObject()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbExistsInDbNotInObject";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);

        // Test
        $success = $list->moveItem(5, 2, true);

        // Verify
        $this->assertEquals(array(1, 2, 3, 4), $list->getItems(), "Items should be 1, 2, 3, 4");
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertFalse($success, "The function should be have error(s)");
        $this->assertEquals(array(1, 5, 2, 3, 4), $items, "Items should be 1, 5, 2, 3, 4");
    }

    /**
     * - No list in the db
     * - Move filmId 4 to before filmId 2
     *
     * Expect
     *   - Returns false (errors)
     *   - Items from the original object: 1, 4, 2, 3, 5
     *   - Items from initFromDb(): none
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbListDoesNotExist()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbListDoesNotExist";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test 2
        $success = $list->moveItem(4, 2, true);

        // Verify 2
        $this->assertEquals(array(1, 4, 2, 3, 5), $list->getItems(), "Items from original object should be 1, 4, 2, 3, 5");
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertFalse($success, "The moveItem() should be have error(s)");
        $this->assertEquals(array(), $items, "Items from the db should be empty");
    }

    /**
     * - List exists in the db with FilmIds 1, 3, 4, 5
     * - List object with the same name: 1, 2, 3, 4, 5
     * - Move filmId 4 to before filmId 2
     *
     * Expect
     *   - Returns false (errors)
     *   - Items from db: 1, 3, 4, 5
     *   - Items for original object: 1, 4, 2, 3, 5
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbNextInObjectNotInDb()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbNextInObjectNotInDb";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(4, 2, true);

        // Verify
        $this->assertEquals(array(1, 4, 2, 3, 5), $list->getItems(), "Items should be 1, 4, 2, 3, 5");
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertFalse($success, "The function should be have error(s)");
        $this->assertEquals(array(1, 3, 4, 5), $items, "Items should be 1, 3, 4, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4
     * - List object with the same name: 1, 2, 3, 4, 5
     * - Move filmId 2 to before filmId 5
     *
     * Expect
     *   - Returns false (errors)
     *   - Items from db: 1, 2, 3, 4
     *   - Items for original object: 1, 3, 4, 2, 5
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbNextInObjectNotInDbToEnd()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbNextInObjectNotInDbToEnd";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->createToDb();
        $list->addItem(5);

        // Test
        $success = $list->moveItem(2, 5, true);

        // Verify
        $this->assertEquals(array(1, 3, 4, 2, 5), $list->getItems(), "Items should be 1, 3, 4, 2, 5");
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertFalse($success, "The function should be have error(s)");
        $this->assertEquals(array(1, 2, 3, 4), $items, "Items should be 1, 2, 3, 4");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - List object with the same name: 1, 3, 4, 5
     * - Move filmId 4 to before filmId 2
     *
     * Expect
     *   - Returns false (errors)
     *   - Items from db: 1, 4, 2, 3, 5
     *   - Items for original object: 1, 3, 4, 5
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbNextInDbNotInObject()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbNextInDbNotInObject";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(4, 2, true);

        // Verify
        $this->assertEquals(array(1, 3, 4, 5), $list->getItems(), "Items should be 1, 3, 4, 5");
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertFalse($success, "The function should be have error(s)");
        $this->assertEquals(array(1, 4, 2, 3, 5), $items, "Items should be 1, 4, 2, 3, 5");
    }

    /**
     * - List exists in the db with FilmIds 1, 2, 3, 4, 5
     * - List object with the same name: 1, 3, 2, 4, 5
     * - Move filmId 4 to before filmId 2
     *
     * Expect
     *   - Returns true (no errors)
     *   - Items from db: 1, 4, 2, 3, 5
     *   - Items for original object: 1, 3, 4, 2, 5
     *
     * @covers  \RatingSync\Filmlist::moveItemInDb()
     * @depends testAddItem
     * @depends testCreateToDb
     * @depends testInitFromDb
     * @depends testGetItems
     */
    public function testMoveItemInDbObjectAndDbOrderDifferent()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = "testMoveItemInDbObjectAndDbOrderDifferent";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->addItem(4);
        $list->addItem(5);
        $list->createToDb();
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(3);
        $list->addItem(2);
        $list->addItem(4);
        $list->addItem(5);

        // Test
        $success = $list->moveItem(4, 2, true);

        // Verify
        $this->assertEquals(array(1, 3, 4, 2, 5), $list->getItems(), "Items should be 1, 3, 4, 2, 5");
        $verifyList = new Filmlist($username, $listname);
        $verifyList->setSort(ListSortField::position);
        $verifyList->setSortDirection(SqlSortDirection::ascending);
        $verifyList->initFromDb();
        $items = $verifyList->getItems();
        $this->assertTrue($success, "The function should be have no error(s)");
        $this->assertEquals(array(1, 4, 2, 3, 5), $items, "Items should be 1, 4, 2, 3, 5");
    }
}

?>