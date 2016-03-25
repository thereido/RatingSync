<?php
/**
 * Temp PHPUnit
 */
namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Constants.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Filmlist.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "Film.php";

require_once "10DatabaseTest.php";
require_once "HttpChild.php";

const TEST_LIST = Constants::LIST_DEFAULT;

class FilmlistTest extends \PHPUnit_Framework_TestCase
{
    public $debug;
    public $timer;

    public function setUp()
    {
        $this->debug = false;
        $this->timer = new \DateTime();
    }

    public function start($className, $functionName)
    {
        if ($this->debug) {
            echo " $className::$functionName ";
            $this->timer = new \DateTime();
        }
    }

    public function tearDown()
    {
        if ($this->debug) { echo $this->timer->diff(date_create())->format('%s secs') . "\n"; }
    }

    /**
     * @covers \RatingSync\Filmlist::__construct
     */
    public function testObjectCanBeConstructed()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
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
     * @expectedException \InvalidArgumentException
     */
    public function testSetListNameNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        
        // Null
        $list->setListName(null);
    }

    /**
     * @covers  \RatingSync\Filmlist::setListName
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testSetListNameEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

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
     * @expectedException \InvalidArgumentException
     */
    public function testSetUsernameNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        
        // Null
        $list->setUsername(null);
    }

    /**
     * @covers  \RatingSync\Filmlist::setUsername
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testSetUsernameEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);

        // Empty String
        $list->setUsername("");
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testAddItemWithNull()
    {$this->start(__CLASS__, __FUNCTION__);

        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(null);
    }

    /**
     * @covers  \RatingSync\Filmlist::addItem
     * @depends testObjectCanBeConstructed
     * @expectedException \InvalidArgumentException
     */
    public function testAddItemWithEmpty()
    {$this->start(__CLASS__, __FUNCTION__);

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

    public function testSetup()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();
        
        DatabaseTest::resetDb();
        
        $http = new HttpChild("empty_username");
        $film = new Film($http);
        $film->setTitle("Filmlist Title 1");
        $film->setYear(2016);
        $film->saveToDb();
        
        $film = new Film($http);
        $film->setTitle("Filmlist Title 2");
        $film->setYear(2016);
        $film->saveToDb();
        
        $film = new Film($http);
        $film->setTitle("Filmlist Title 3");
        $film->setYear(2016);
        $film->saveToDb();
        
        $film = new Film($http);
        $film->setTitle("Filmlist Title 4");
        $film->setYear(2016);
        $film->saveToDb();
    }

    /**
     * @covers  \RatingSync\Filmlist::saveToDb
     * @depends testObjectCanBeConstructed
     * @depends testSetup
     * @depends testAddItem
     */
    public function testSaveToDb()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();

        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $listname = TEST_LIST;
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);

        // Test
        $list->saveToDb();

        // Verify
        $query = "SELECT * FROM filmlist WHERE user_name='$username' AND listname='$listname' ORDER BY position ASC";
        $result = $db->query($query);
        $this->assertEquals(2, $result->num_rows, "Should be 2 films in the list");
        $this->assertEquals(1, $result->fetch_assoc()['film_id'], "Position 1 should filmId 1");
        $this->assertEquals(2, $result->fetch_assoc()['film_id'], "Position 2 should filmId 2");
    }

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
     * @covers  \RatingSync\Filmlist::initFromDb
     * @depends testAddItem
     * @depends testSaveToDb
     * @depends testGetItems
     */
    public function testInitFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testInitFromDb";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->saveToDb();

        // Test
        $listDb = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $listDb->initFromDb();

        // Verify
        $this->assertEquals($list->getItems(), $listDb->getItems(), "Original items should match items from db");
    }

    /**
     * @covers  \RatingSync\Filmlist::getListFromDb
     * @depends testAddItem
     * @depends testSaveToDb
     * @depends testInitFromDb
     */
    public function testGetListFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testGetListFromDb";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->saveToDb();

        // Test
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);

        // Verify
        $this->assertEquals($list->getItems(), $listDb->getItems(), "Original items should match items from db");
    }

    /**
     * @covers  \RatingSync\Filmlist::removeFromDb
     * @depends testAddItem
     * @depends testSaveToDb
     * @depends testGetListFromDb
     * @depends testCount
     */
    public function testRemoveFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testRemoveFromDb";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->saveToDb();

        // Test
        $list->removeFromDb();

        // Verify
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $this->assertEquals(0, $listDb->count());
    }

    /**
     * @covers  \RatingSync\Filmlist::removeListFromDb
     * @depends testAddItem
     * @depends testSaveToDb
     * @depends testRemoveFromDb
     * @depends testGetListFromDb
     * @depends testCount
     */
    public function testRemoveFilmlistFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testRemoveListFromDb";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->saveToDb();

        // Test
        Filmlist::removeListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);

        // Verify
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $this->assertEquals(0, $listDb->count());
    }

    /**
     * @covers  \RatingSync\Filmlist::saveToDb
     * @depends testAddItem
     */
    public function testSaveToDbUsernameNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $listname = "testSaveToDbUsernameNotFound";
        $list = new Filmlist("bad_username", $listname);
        $list->addItem(1);
        $list->addItem(2);

        // Test
        $success = $list->saveToDb();

        // Verify
        $this->assertFalse($success, "Should not succeed");
    }

    /**
     * @covers  \RatingSync\Filmlist::saveToDb
     * @depends testAddItem
     * @depends testSaveToDb
     * @depends testGetListFromDb
     * @depends testRemoveItem
     * @depends testGetItems
     */
    public function testSaveToDbBadFilmId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $listname = "testSaveToDbBadFilmId";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(666);

        // Test
        $success = $list->saveToDb();

        // Verify
        $this->assertFalse($success, "Should not succeed");
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->removeItem(666);
        $this->assertEquals($list->getItems(), $listDb->getItems(), "Valid items should be saved");
    }

    /**
     * @covers  \RatingSync\Filmlist::saveToDb
     * @depends testAddItem
     * @depends testSaveToDb
     * @depends testGetListFromDb
     * @depends testGetItems
     */
    public function testSaveToDbSecondList()
    {$this->start(__CLASS__, __FUNCTION__);

        // Set up
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $list->addItem(1);
        $list->addItem(2);
        $list2 = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, "list2");
        $list2->addItem(3);

        // Test
        $list->saveToDb();
        $list2->saveToDb();

        // Verify
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, TEST_LIST);
        $this->assertEquals($list->getItems(), $listDb->getItems(), "First list should match");
        $listDb2 = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, "list2");
        $this->assertEquals($list2->getItems(), $listDb2->getItems(), "Seconds list should match");
    }

    /**
     * @covers  \RatingSync\Filmlist::saveToDb
     * @depends testAddItem
     * @depends testSaveToDb
     * @depends testGetListFromDb
     * @depends testGetItems
     */
    public function testSaveToDbExistingListAddFilm()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testSaveToDbExistingListAddFilm";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->saveToDb();

        // Test
        $list->addItem(3);
        $list->saveToDb();

        // Verify
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $this->assertEquals(3, $listDb->count(), "Should be 3 items");
        $this->assertEquals($list->getItems(), $listDb->getItems(), "The 2 original items plus the 1 new item should match items from db");
    }

    /**
     * @covers  \RatingSync\Filmlist::saveToDb
     * @depends testAddItem
     * @depends testSaveToDb
     * @depends testGetListFromDb
     * @depends testRemoveItem
     * @depends testGetItems
     * @depends testCount
     */
    public function testSaveToDbExistingListRemoveFilm()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testSaveToDbExistingListRemoveFilm";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->saveToDb();

        // Test
        $list->removeItem(1);
        $list->saveToDb();

        // Verify
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $this->assertEquals(1, $listDb->count(), "Should be 1 item");
        $this->assertEquals($list->getItems(), $listDb->getItems(), "The 1 remaining item should match items from db");
    }

    /**
     * @covers  \RatingSync\Filmlist::saveToDb
     * @depends testSaveToDbExistingListAddFilm
     * @depends testSaveToDbExistingListRemoveFilm
     */
    public function testSaveToDbExistingListAddAndRemoveFilms()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testSaveToDbExistingListAddAndRemoveFilms";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(3);
        $list->addItem(1);
        $list->addItem(2);
        $list->saveToDb();

        // Test
        $list->removeItem(1);
        $list->addItem(4);
        $list->saveToDb();

        // Verify
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $this->assertEquals(array(3, 2, 4), $listDb->getItems(), "Start with 3,1,2 - remove 1 - add 4 - End with 3,2,4");
    }

    /**
     * @covers  \RatingSync\Filmlist::saveToDb
     * @depends testAddItem
     * @depends testSaveToDb
     * @depends testGetListFromDb
     * @depends testRemoveItem
     * @depends testGetItems
     */
    public function testSaveToDbChangePosition()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testSaveToDbChangePosition";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->saveToDb();

        // Test
        $list->removeItem(2);
        $list->addItem(2);
        $list->saveToDb();

        // Verify
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $this->assertEquals(3, $listDb->count(), "Should be 3 items");
        $this->assertEquals(array(1, 3, 2), $listDb->getItems(), "Should move 2 to the end");
    }

    /**
     * @covers  \RatingSync\Filmlist::saveToDb
     * @depends testSaveToDbExistingListAddFilm
     * @depends testSaveToDbExistingListRemoveFilm
     * @depends testSaveToDbChangePosition
     */
    public function testSaveToDbAddRemoveChangePosition()
    {$this->start(__CLASS__, __FUNCTION__);
    
        // Set up
        $listname = "testSaveToDbAddRemoveChangePosition";
        $list = new Filmlist(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->addItem(3);
        $list->saveToDb();

        // Test
        $list->removeItem(2);
        $list->addItem(4);
        $list->removeItem(1);
        $list->addItem(1);
        $list->saveToDb();

        // Verify
        $listDb = Filmlist::getListFromDb(Constants::TEST_RATINGSYNC_USERNAME, $listname);
        $this->assertEquals(3, $listDb->count(), "Should be 3 items");
        $this->assertEquals(array(3, 4, 1), $listDb->getItems(), "Remove 2, Add 4, Move 1 to the end (3,4,1)");
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
        $this->assertTrue($db->query($query));

        // Set no lists

        // Test
        $lists = Filmlist::getUserListsFromDb($username);

        // Verify
        $this->assertEquals(1, count($lists), "Should be a default list");
    }

    /**
     * @covers  \RatingSync\Filmlist::getUserListsFromDb
     * @depends testSaveToDbSecondList
     * @depends testGetUserListsFromDbOnlyDefault
     */
    public function testGetUserListsFromDb()
    {$this->start(__CLASS__, __FUNCTION__);
        $db = getDatabase();
    
        // Set up
        $username = Constants::TEST_RATINGSYNC_USERNAME;
        $query = "DELETE FROM filmlist WHERE user_name='$username'";
        $this->assertTrue($db->query($query));

        $listname = "testUserGetListsFromDb";
        $list = new Filmlist($username, $listname);
        $list->addItem(1);
        $list->addItem(2);
        $list->saveToDb();

        $listname2 = "anotherList";
        $list2 = new Filmlist($username, $listname2);
        $list2->addItem(2);
        $list2->addItem(3);
        $list2->addItem(4);
        $list2->saveToDb();

        // Test
        $lists = Filmlist::getUserListsFromDb($username);

        // Verify
        $this->assertEquals(3, count($lists), "Should be 3 lists (2 set here plus the default 1");
        $this->assertTrue(array_key_exists($listname, $lists), "First list ($listname) should be in the db");
        $this->assertTrue(array_key_exists($listname2, $lists), "Second list ($listname2) should be in the db");
        $this->assertEquals($list->getItems(), $lists[$listname]->getItems(), "First list should match the db");
        $this->assertEquals($list2->getItems(), $lists[$listname2]->getItems(), "Second list should match the db");
    }
}

?>