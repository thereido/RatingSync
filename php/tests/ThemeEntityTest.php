<?php

namespace RatingSync;

use Exception;
use InvalidArgumentException;
use TypeError;

require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entity" .DIRECTORY_SEPARATOR. "Entities" .DIRECTORY_SEPARATOR. "ThemeEntity.php";
require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entity" .DIRECTORY_SEPARATOR. "Entities" .DIRECTORY_SEPARATOR. "ThemeProperty.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

class ThemeEntityTest extends RatingSyncTestCase
{

    const NEW_ENTITY_ID = EntityManager::NEW_ENTITY_ID;
    const PRIMARY_THEME_ID = 1;
    const PRIMARY_NAME = "dark";
    const PRIMARY_ENABLED = true;

    /* Uncomment if you want to disable verbose mode
    protected function setUp(): void
    {
        parent::setup();
        $this->verbose = false;
    }
    */

    private function primaryEntity($newEntity = false): ThemeEntity
    {
        $id = $newEntity ? null : self::PRIMARY_THEME_ID;

        return new ThemeEntity(
            $id,
            self::PRIMARY_NAME,
            self::PRIMARY_ENABLED
        );
    }

    public function testResetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers \RatingSync\ThemeEntity::__construct
     */
    public function test__construct()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        // Test
        $entity = $this->primaryEntity();

        // Verify
        $this->assertInstanceOf(ThemeEntity::class, $entity, "\$entity should be a ThemeEntity");
        $this->assertSame(self::PRIMARY_THEME_ID, $entity->id, "Theme ID");
        $this->assertSame(self::PRIMARY_NAME, $entity->name, "Theme name");
        $this->assertSame(self::PRIMARY_ENABLED, $entity->enabled, "User->enabled");
    }

    /**
     * - Constructor with a null name
     *
     * Expect:
     * - \TypeError
     *
     * @covers \RatingSync\ThemeEntity::__construct
     * @depends test__construct
     */
    public function test__construct_NullName()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(TypeError::class);

        // Test
        $entity = new ThemeEntity(self::PRIMARY_THEME_ID, null, self::PRIMARY_ENABLED);
    }

    /**
     * - Constructor with an empty name
     *
     * Expect:
     * - \InvalidArgumentException
     *
     * @covers \RatingSync\ThemeEntity::__construct
     * @depends test__construct
     */
    public function test__construct_EmptyName()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(InvalidArgumentException::class);

        // Test
        $entity = new ThemeEntity(self::PRIMARY_THEME_ID, "", self::PRIMARY_ENABLED);
    }

    /**
     * - Constructor with no args
     *
     * Expect:
     * - \TypeError
     *
     * @covers \RatingSync\ThemeEntity::__construct
     * @depends test__construct
     */
    public function test__construct_NoArgs()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(TypeError::class);

        // Test
        $entity = new ThemeEntity();
    }

    /**
     * - Constructor with a name that is not in the db
     *
     * Expect:
     * - Values should match
     *
     * @covers \RatingSync\ThemeEntity::__construct
     * @depends test__construct
     */
    public function test__construct_NonExistingName()
    {$this->start(__CLASS__, __FUNCTION__);

        // Test
        try {

            $entity = new ThemeEntity(
                self::PRIMARY_THEME_ID,
                "NonExisting Name",
                self::PRIMARY_ENABLED
            );

        }
        catch (Exception $e) {
            $this->assertTrue(false, "ThemeEntity Constructor.\n" . $e->getMessage());
        }

        // Verify
        $this->assertInstanceOf(ThemeEntity::class, $entity, "\$entity should be a ThemeEntity");
        $this->assertSame(self::PRIMARY_THEME_ID, $entity->id, "Theme ID");
        $this->assertSame("NonExisting Name", $entity->name, "Theme name");
        $this->assertSame(self::PRIMARY_ENABLED, $entity->enabled, "User->enabled");
    }

    /**
     * - Primary entity with no changes
     *
     * Expect:
     * - No exceptions
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test__construct
     */
    public function test_save()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity = $this->primaryEntity();
        $returnCode = false;

        // Test
        try {
            $returnCode = $entity->save();
        }
        catch (Exception|EntityInvalidSaveException $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify

        $id = $returnCode;
        $this->assertTrue( $returnCode !== false, "Save should not fail" );
        $this->assertEquals(self::PRIMARY_THEME_ID, $id, "Primary entity id should be returned");
    }

    /**
     * - Primary entity with no changes
     *
     * Expect:
     * - Values from the db match
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_save
     */
    public function test_save_NoChanges()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $entity = $this->primaryEntity();
        $returnCode = false;

        // Test

        try {
            $returnCode = $entity->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify

        $id = $returnCode;
        $this->assertTrue( $returnCode !== false, "Save should not fail" );
        $this->assertEquals(self::PRIMARY_THEME_ID, $id, "Primary entity id should be returned");
        $dbEntity = themeMgr()->findWithId(self::PRIMARY_THEME_ID);
        $this->assertInstanceOf(ThemeEntity::class, $dbEntity, "\$dbEntity should be an entity");
        $this->assertEquals(self::PRIMARY_THEME_ID, $dbEntity->id, "Entity ID");
        $this->assertEquals(self::PRIMARY_NAME, $dbEntity->name, "Name");
        $this->assertEquals(self::PRIMARY_ENABLED, $dbEntity->enabled, "Enable");
// FIXME $this->assertTrue(false, "This assert is not implemented: Use ts to make sure it did not do the update statement");
    }

    /**
     * - Primary entity with changed name
     *
     * Expect:
     * - Values from the db match
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_save_NoChanges
     */
    public function test_save_ChangeName()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->testResetDb();

        $newName = "Beige";
        $entity = $this->primaryEntity();
        $returnCode = false;
        $changed = new ThemeEntity(
            $entity->id,
            $newName,
            $entity->enabled);

        // Test

        try {
            $returnCode = $changed->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify

        $id = $returnCode;
        $this->assertTrue( $returnCode !== false, "Save should not fail" );
        $this->assertEquals(self::PRIMARY_THEME_ID, $id, "Primary entity id should be returned");
        $dbEntity = themeMgr()->findWithName($newName);
        $this->assertInstanceOf(ThemeEntity::class, $dbEntity, "\$dbEntity should be an Entity");
        $this->assertEquals(self::PRIMARY_THEME_ID, $dbEntity->id, "Entity ID");
        $this->assertEquals($newName, $dbEntity->name, "Name");
        $this->assertEquals(self::PRIMARY_ENABLED, $dbEntity->enabled, "Enabled");
    }

    /**
     * - Primary entity change: Enabled
     *
     * Expect:
     * - Values from the db match
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_save_NoChanges
     */
    public function test_save_ChangeEnabled()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->testResetDb();

        $newEnabled = !self::PRIMARY_ENABLED;
        $entity = $this->primaryEntity();
        $returnCode = false;
        $changed = new ThemeEntity(
            $entity->id,
            $entity->name,
            $newEnabled);

        // Test

        try {
            $returnCode = $changed->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify

        $id = $returnCode;
        $this->assertTrue( $returnCode !== false, "Save should not fail" );
        $this->assertEquals(self::PRIMARY_THEME_ID, $id, "Primary entity id should be returned");
        $dbEntity = themeMgr()->findWithName(self::PRIMARY_NAME, true);
        $this->assertInstanceOf(ThemeEntity::class, $dbEntity, "\$dbEntity should be an Entity");
        $this->assertEquals($changed->id, $dbEntity->id, "Entity ID");
        $this->assertEquals($changed->name, $dbEntity->name, "Name");
        $this->assertEquals($newEnabled, $dbEntity->enabled, "Enabled");
        $this->assertEquals($changed->enabled, $dbEntity->enabled, "Enabled");
    }

    /**
     * - Primary entity with all fields changed except ID
     *
     * Expect:
     * - Values from the db match
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_save_NoChanges
     */
    public function test_save_ChangeAll()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->testResetDb();

        $newName = "newNameForChangeAll";
        $newEnabled = !self::PRIMARY_ENABLED;

        $entity = $this->primaryEntity();
        $returnCode = false;
        $changed = new ThemeEntity(
            $entity->id,
            $newName,
            $newEnabled);

        // Test

        try {
            $returnCode = $changed->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify

        $id = $returnCode;
        $this->assertTrue( $returnCode !== false, "Save should not fail" );
        $this->assertEquals(self::PRIMARY_THEME_ID, $id, "Primary entity id should be returned");
        $dbEntity = themeMgr()->findWithName($newName, true);
        $this->assertInstanceOf(ThemeEntity::class, $dbEntity, "\$dbEntity should be an Entity");
        $this->assertEquals($changed->id, $dbEntity->id, "Entity ID");
        $this->assertEquals($changed->name, $dbEntity->name, "Name");
        $this->assertEquals($changed->enabled, $dbEntity->enabled, "Enabled");
    }

    /**
     * - New entity with valid properties
     *
     * Expect:
     * - No exceptions
     * - Get a new db id
     * - Values from the db match
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_save
     */
    public function test_save_NewEntity()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->testResetDb();

        $returnCode = false;
        $name = "theme1";
        $enabled = true;

        $entity = new ThemeEntity(
            null,
            $name,
            $enabled);

        // Test

        try {
            $returnCode = $entity->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify

        $id = $returnCode;
        $this->assertTrue( $returnCode !== false, "Save should not fail" );
        $dbEntity = themeMgr()->findWithName($name);
        $this->assertInstanceOf(ThemeEntity::class, $dbEntity, "\$dbEntity should be an Entity");
        $this->assertIsInt($id, "ID returned should be an int");
        $this->assertGreaterThan(0, $id, "ID returned should be greater than 0");
        $this->assertEquals($id, $dbEntity->id, "ID returned should be match the db entity ID");
        $this->assertEquals($entity->name, $dbEntity->name, "Name");
        $this->assertEquals($entity->enabled, $dbEntity->enabled, "Enabled");
    }

    /**
     * - New entity with a name that was already taken
     *
     * Expect:
     * - EntityInvalidSaveException
     * - Verify the exception message
     * - One invalid property (name)
     * - Verify the invalid property message
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_save_NewEntity
     */
    public function test_save_NameTaken()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(EntityInvalidSaveException::class);

        $this->testResetDb();

        $exceptionMsg = "Cannot save the entity with these values";
        $invalidProperty = ThemeProperty::Name;
        $name = self::PRIMARY_NAME;
        $propertyMsgExpected = $invalidProperty->name . " ($name) is already taken";

        $enabled = true;

        $entity = new ThemeEntity(
            null,
            $name,
            $enabled);

        // Test

        try {
            $entity->save();
        }
        catch (EntityInvalidSaveException $e) {

            // Verify

            $this->assertStringContainsString($exceptionMsg, $e->getMessage());
            $this->assertCount(1, $entity->invalidProperties(), "There should be 1 invalid property");
            $this->assertEquals($invalidProperty->name, $entity->invalidProperties()[0], $invalidProperty->name . " should be an invalid property");
            $propertyMsg = $entity->invalidPropertyMessage($invalidProperty->name);
            $this->assertEquals($propertyMsgExpected, $propertyMsg, "Wrong error message for an invalid " . $invalidProperty->name);

            throw $e;

        }
    }

    /**
     * - New entity with a name that is too long
     *
     * Expect:
     * - EntityInvalidSaveException
     * - Verify the exception message
     * - One invalid property (name)
     * - Verify the invalid property message
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_save_NewEntity
     */
    public function test_save_NameMax()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(EntityInvalidSaveException::class);

        $this->testResetDb();

        $exceptionMsg = "Cannot save the entity with these values";
        $invalidProperty = ThemeProperty::Name;
        $propertyMsgExpected = $invalidProperty->name . " max length is " . ThemeProperty::nameMax();

        $name = "1111111111_2222222222_3333333333_4444444444_5555555555";
        $enabled = true;

        $entity = new ThemeEntity(
            null,
            $name,
            $enabled);

        // Test

        try {
            $entity->save();
        }
        catch (EntityInvalidSaveException $e) {

            // Verify

            $this->assertStringContainsString($exceptionMsg, $e->getMessage());
            $this->assertCount(1, $entity->invalidProperties(), "There should be 1 invalid property");
            $this->assertEquals($invalidProperty->name, $entity->invalidProperties()[0], $invalidProperty->name . " should be an invalid property");
            $propertyMsg = $entity->invalidPropertyMessage($invalidProperty->name);
            $this->assertEquals($propertyMsgExpected, $propertyMsg, "Wrong error message for an invalid " . $invalidProperty->name);

            throw $e;

        }
    }

    /**
     * - Try to change an entity with an Id that is not in the db
     *
     * Expect:
     * - EntityInvalidSaveException
     * - Verify the exception message
     * - One invalid property (Id)
     * - Verify the invalid property message
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_save_NewEntity
     */
    public function testSave_IdNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(EntityInvalidSaveException::class);

        $this->testResetDb();

        $exceptionMsg = "Cannot save the entity with these values";
        $invalidProperty = ThemeProperty::Id;
        $id = 1000;
        $propertyMsgExpected = $invalidProperty->name . " " . $id . " not found";

        $name = self::PRIMARY_NAME;
        $enabled = self::PRIMARY_ENABLED;

        $entity = new ThemeEntity(
            $id,
            $name,
            $enabled);

        // Test

        try {
            $entity->save();
        }
        catch (EntityInvalidSaveException $e) {

            // Verify

            $this->assertStringContainsString($exceptionMsg, $e->getMessage());
            $this->assertCount(1, $entity->invalidProperties(), "There should be 1 invalid property");
            $this->assertEquals($invalidProperty->name, $entity->invalidProperties()[0], $invalidProperty->name . " should be an invalid property");
            $propertyMsg = $entity->invalidPropertyMessage($invalidProperty->name);
            $this->assertEquals($propertyMsgExpected, $propertyMsg, "Wrong error message for an invalid " . $invalidProperty->name);

            throw $e;

        }
    }

    /**
     * - 2 entities with the same properties
     *
     * Expect:
     * - Return true
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test__construct
     */
    public function test_equals()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 = $this->primaryEntity();
        $entity2 = $this->primaryEntity();

        // Test
        $equals = $entity1->equals($entity2);

        $this->assertTrue($equals, "The 2 entities should be equals");
    }

    /**
     * - 2 entities has one property different: id
     *
     * Expect:
     * - Return false
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_equals
     */
    public function test_equals_DiffId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 = $this->primaryEntity();
        $entity2 = new ThemeEntity(
            1000,
            self::PRIMARY_NAME,
            self::PRIMARY_ENABLED
        );

        // Test
        $equals = $entity1->equals($entity2);

        $this->assertFalse($equals, "The 2 entities should not be equals");
    }

    /**
     * - 2 entities has one property different: name
     *
     * Expect:
     * - Return false
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_equals
     */
    public function test_equals_DiffName()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 = $this->primaryEntity();
        $entity2 = new ThemeEntity(
            self::PRIMARY_THEME_ID,
            "beige",
            self::PRIMARY_ENABLED
        );

        // Test
        $equals = $entity1->equals($entity2);

        $this->assertFalse($equals, "The 2 entities should not be equals");
    }

    /**
     * - 2 entities has one property different: enabled
     *
     * Expect:
     * - Return false
     *
     * @covers \RatingSync\ThemeEntity::save
     * @depends test_equals
     */
    public function test_equals_DiffEnabled()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 = $this->primaryEntity();
        $entity2 = new ThemeEntity(
            self::PRIMARY_THEME_ID,
            self::PRIMARY_NAME,
            !self::PRIMARY_ENABLED
        );

        // Test
        $equals = $entity1->equals($entity2);

        $this->assertFalse($equals, "The 2 entities should not be equals");
    }

}
