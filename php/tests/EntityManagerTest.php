<?php

namespace RatingSync;

require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entity" .DIRECTORY_SEPARATOR. "Managers" . DIRECTORY_SEPARATOR . "EntityManager.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entity" .DIRECTORY_SEPARATOR. "Managers" . DIRECTORY_SEPARATOR . "ThemeManager.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

use \PDO;

// Class to expose protected members and functions
class EntityManagerChild extends EntityManager {

    function _getDb(): PDO { return $this->getDb(); }
    function _findWithQuery( string $query ): Entity|false { return $this->findWithQuery($query); }
    function _findMultipleDbResult( string $query ): array|false { return $this->findMultipleDbResult($query); }
    function _boolFromInt( $int ): bool { return $this->boolFromInt($int); }
    function _entityFromRow( array $row ): Entity { return $this->entityFromRow($row); }

    protected function mandatoryColumns(): array
    {
        return ThemeEntity::mandatoryColumns();
    }

    /**
     * @return This uses ignores the $row param and return a ThemeEntity.
     */
    protected function entityFromRow(array $row): Entity
    {
        // This is copied from ThemeManager::entityFromRow()

        $id = $row["id"];
        $name = $row["name"];
        $enabled = $row["enabled"];

        return new ThemeEntity($id, $name, $enabled);
    }

    public function destructDbConnection() {
        // https://www.php.net/manual/en/pdo.connections.php
        self::$db = null;
    }

}

class EntityManagerTest extends RatingSyncTestCase
{

    protected function setUp(): void
    {
        parent::setup();
        //$this->verbose = true;
    }

    public function testDestructDbConnection()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();

        // Test
        $mgr->destructDbConnection();

        // Verify
        $ifDidNotBlowUp = true;
        $this->assertTrue($ifDidNotBlowUp);
    }

    /**
     * @covers  \RatingSync\EntityManager::getDb
     * @depends testDestructDbConnection
     */
    public function testGetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();
        $mgr->destructDbConnection();

        // Test
        $db = $mgr->_getDb();

        // Verify
        $this->assertInstanceOf(PDO::class, $db, "Did not get a PDO connection");
    }

    /**
     * @covers  \RatingSync\EntityManager::boolFromInt
     */
    public function testBoolFromIntZero()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();

        // Test
        $bool = $mgr->_boolFromInt(0);

        // Verify
        $this->assertFalse($bool, "Zero should be false");
    }

    /**
     * @covers  \RatingSync\EntityManager::boolFromInt
     */
    public function testBoolFromIntOne()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();

        // Test
        $bool = $mgr->_boolFromInt(1);

        // Verify
        $this->assertTrue($bool, "One should be true");
    }

    /**
     * @covers  \RatingSync\EntityManager::boolFromInt
     * @depends testBoolFromIntOne
     */
    public function testBoolFromIntPositive()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();

        // Test
        $bool = $mgr->_boolFromInt(5);

        // Verify
        $this->assertTrue($bool, "5 (positive) should be true");
    }

    /**
     * @covers  \RatingSync\EntityManager::boolFromInt
     * @depends testBoolFromIntPositive
     */
    public function testBoolFromIntNegative()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();

        // Test
        $bool = $mgr->_boolFromInt(-1);

        // Verify
        $this->assertFalse($bool, "Negative number should be false");
    }

    /**
     * @covers  \RatingSync\EntityManager::boolFromInt
     */
    public function testBoolFromIntTrue()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();

        // Test
        $bool = $mgr->_boolFromInt(true);

        // Verify
        $this->assertTrue($bool, "true should be true");
    }

    /**
     * @covers  \RatingSync\EntityManager::boolFromInt
     */
    public function testBoolFromIntFalse()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();

        // Test
        $bool = $mgr->_boolFromInt(false);

        // Verify
        $this->assertFalse($bool, "false should be false");
    }

    /**
     * @covers  \RatingSync\EntityManager::findWithQuery
     * @depends testGetDb
     */
    public function testFindWithQuery()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();
        $query = "SELECT * FROM theme WHERE id=1";

        // Test
        $entity = $mgr->_findWithQuery($query);

        // Verify
        $this->assertTrue($entity !== false, "Response should not be false");
        $this->assertTrue($entity instanceof Entity, "\$entity should be an Entity");
    }

    /**
     * @covers  \RatingSync\EntityManager::findWithQuery
     * @depends testFindWithQuery
     */
    public function testFindWithQueryInvalidSql()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();
        $query = "SELECT * FROM";

        // Test
        $entity = $mgr->_findWithQuery($query);

        // Verify
        $this->assertTrue($entity === false, "Response should be false");
    }

    /**
     * @covers  \RatingSync\EntityManager::findWithQuery
     * @depends testFindWithQuery
     */
    public function testFindWithQueryWrongTable()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild(); // EntityManagerChild uses ThemeManager
        $query = "SELECT * FROM user WHERE id=1";

        // Test
        $entity = $mgr->_findWithQuery($query);

        // Verify
        $this->assertTrue($entity === false, "Response should be false");
    }

    /**
     * @covers  \RatingSync\EntityManager::findWithQuery
     * @depends testFindWithQuery
     */
    public function testFindWithQueryMultipleRows()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();
        $query = "SELECT * FROM theme";

        // Test
        $entity = $mgr->_findWithQuery($query);

        // Verify
        $this->assertTrue($entity === false, "Response should be false");
    }

    /**
     * @covers  \RatingSync\EntityManager::findWithQuery
     * @depends testFindWithQuery
     */
    public function testFindWithQueryNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();
        $query = "SELECT * FROM theme WHERE id=1000";

        // Test
        $entity = $mgr->_findWithQuery($query);

        // Verify
        $this->assertTrue($entity === false, "Response should be false");
    }

    /**
     * @covers  \RatingSync\EntityManager::findMultipleDbResult
     * @depends testGetDb
     */
    public function testFindMultipleDbResult()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();
        $query = "SELECT * FROM theme";

        // Test
        $entities = $mgr->_findMultipleDbResult($query);

        // Verify
        $this->assertTrue($entities !== false, "Result should not be false");
        $count = count($entities);
        $this->assertGreaterThan(1, $count, "There should be multiple entities. Got '$count'.");
        foreach ($entities as $entity) {
            $this->assertInstanceOf(ThemeEntity::class, $entity, "mgr->entityFromRow( \$entity ) should give a ThemeEntity");
        }
    }

    /**
     * @covers  \RatingSync\EntityManager::findMultipleDbResult
     * @depends testFindMultipleDbResult
     */
    public function testFindMultipleDbResult_1match()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();
        $query = "SELECT * FROM theme WHERE id=1";

        // Test
        $entities = $mgr->_findMultipleDbResult($query);

        // Verify
        $this->assertTrue($entities !== false, "Result should not be false");
        $count = count($entities);
        $this->assertEquals(1, $count, "Should be one entity. Got '$count'.");
        foreach ($entities as $entity) {
            $this->assertInstanceOf(ThemeEntity::class, $entity, "mgr->entityFromRow( \$entity ) should give a ThemeEntity");
        }
    }

    /**
     * @covers  \RatingSync\EntityManager::findMultipleDbResult
     * @depends testFindMultipleDbResult
     */
    public function testFindMultipleDbResult_InvalidSql()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();
        $query = "SELECT * FROM";

        // Test
        $result = $mgr->_findMultipleDbResult($query);

        // Verify
        $this->assertTrue($result === false, "Result should be false");
    }

    /**
     * @covers  \RatingSync\EntityManager::findMultipleDbResult
     * @depends testFindMultipleDbResult
     */
    public function testFindMultipleDbResult_WrongTable()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $mgr = new EntityManagerChild();
        $query = "SELECT * FROM user";

        // Test
        $result = $mgr->_findMultipleDbResult($query);

        // Verify
        $this->assertTrue($result === false, "Result should be false");
    }

}
