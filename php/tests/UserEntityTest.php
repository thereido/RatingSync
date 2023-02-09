<?php

namespace RatingSync;

use Exception;
use InvalidArgumentException;

require_once __DIR__ .DIRECTORY_SEPARATOR. ".." .DIRECTORY_SEPARATOR. "Entity" .DIRECTORY_SEPARATOR. "Entities" .DIRECTORY_SEPARATOR. "UserEntity.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";
require_once "UserFactoryTest.php";

class UserEntityTest extends RatingSyncTestCase
{

    const NEW_USER_ID = UserFactoryTest::NEW_USER_ID;
    const PRIMARY_USER_ID = UserFactoryTest::PRIMARY_USER_ID;
    const PRIMARY_USERNAME = UserFactoryTest::PRIMARY_USERNAME;
    const PRIMARY_EMAIL = UserFactoryTest::PRIMARY_EMAIL;
    const PRIMARY_ENABLED = UserFactoryTest::PRIMARY_ENABLED;
    const PRIMARY_THEME_ID = UserFactoryTest::PRIMARY_THEME_ID;

    /* Uncomment if you want to disable verbose mode
    protected function setUp(): void
    {
        parent::setup();
        $this->verbose = false;
    }
    */

    private function primaryEntity(): UserEntity
    {
        return new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            self::PRIMARY_THEME_ID);
    }

    public function testResetDb()
    {$this->start(__CLASS__, __FUNCTION__);

        DatabaseTest::resetDb();

        $this->assertTrue(true); // Making sure we made it this far
    }

    /**
     * @covers \RatingSync\UserEntity::__construct
     * @depends testResetDb
     */
    public function testConstructor()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        // Test
        $entity = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            self::PRIMARY_THEME_ID);

        // Verify
        $this->assertInstanceOf(UserEntity::class, $entity, "\$entity should be a UserEntity");
        $this->assertEquals(self::PRIMARY_USER_ID, $entity->id, "User ID");
        $this->assertEquals(self::PRIMARY_USERNAME, $entity->username, "Username");
        $this->assertEquals(self::PRIMARY_EMAIL, $entity->email, "Email");
        $this->assertEquals(self::PRIMARY_ENABLED, $entity->enabled, "User->enabled");
        $this->assertEquals(self::PRIMARY_THEME_ID, $entity->themeId, "Theme ID");
    }

    /**
     * @covers \RatingSync\UserEntity::__construct
     * @depends testConstructor
     */
    public function testConstructor_ForNewUser()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        // Test
        try {

            $entity = new UserEntity(
                self::NEW_USER_ID,
                self::PRIMARY_USERNAME,
                self::PRIMARY_EMAIL,
                self::PRIMARY_ENABLED,
                self::PRIMARY_THEME_ID);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "UserEntity Constructor.\n" . $e->getMessage());
        }

        // Verify
        $this->assertInstanceOf(UserEntity::class, $entity, "\$entity should be a UserEntity");
        $this->assertEquals(self::NEW_USER_ID, $entity->id, "User ID");
        $this->assertEquals(self::PRIMARY_USERNAME, $entity->username, "Username");
        $this->assertEquals(self::PRIMARY_EMAIL, $entity->email, "Email");
        $this->assertEquals(self::PRIMARY_ENABLED, $entity->enabled, "User->enabled");
        $this->assertEquals(self::PRIMARY_THEME_ID, $entity->themeId, "Theme ID");
    }

    /**
     * @covers \RatingSync\UserEntity::__construct
     * @depends testConstructor
     */
    public function testConstructor_NullId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        // Test
        $entity = new UserEntity(
            null,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            self::PRIMARY_THEME_ID);

        // Verify
        $this->assertInstanceOf(UserEntity::class, $entity, "\$entity should be a UserEntity");
        $this->assertEquals(self::NEW_USER_ID, $entity->id, "Sending a null ID to the UserEntity should give the new entity id=-1");
        $this->assertEquals(self::PRIMARY_USERNAME, $entity->username, "Username");
        $this->assertEquals(self::PRIMARY_EMAIL, $entity->email, "Email");
        $this->assertEquals(self::PRIMARY_ENABLED, $entity->enabled, "User->enabled");
        $this->assertEquals(self::PRIMARY_THEME_ID, $entity->themeId, "Theme ID");
    }

    /**
     * @covers \RatingSync\UserEntity::__construct
     * @depends testConstructor
     */
    public function testConstructor_NullThemeId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        // Test
        $entity = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            NULL);

        // Verify
        $this->assertInstanceOf(UserEntity::class, $entity, "\$entity should be a UserEntity");
        $this->assertEquals(self::PRIMARY_USER_ID, $entity->id, "User ID");
        $this->assertEquals(self::PRIMARY_USERNAME, $entity->username, "Username");
        $this->assertEquals(self::PRIMARY_EMAIL, $entity->email, "Email");
        $this->assertEquals(self::PRIMARY_ENABLED, $entity->enabled, "User->enabled");
        $this->assertNull($entity->themeId, "Theme ID should be null");
    }

    /**
     * @covers \RatingSync\UserEntity::__construct
     * @depends testConstructor
     */
    public function testConstructor_InvalidId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(InvalidArgumentException::class);

        // Test

        $entity = new UserEntity(
            -2,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            self::PRIMARY_THEME_ID);

        // Verify
    }

    /**
     * @covers \RatingSync\UserEntity::__construct
     * @depends testConstructor
     */
    public function testConstructor_InvalidThemeIdNegative()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(InvalidArgumentException::class);

        // Test

        $entity = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            -1);

        // Verify
    }

    /**
     * @covers \RatingSync\UserEntity::__construct
     * @depends testConstructor
     */
    public function testConstructor_InvalidThemeIdZero()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(InvalidArgumentException::class);

        // Test

        $entity = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            0);

        // Verify
    }

    /**
     * @covers \RatingSync\UserEntity::mandatoryColumns
     */
    public function testMandatoryColumns()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        // Test
        $columns = UserEntity::mandatoryColumns();

        // Verify
        $this->assertSame(UserEntity::mandatoryColumns, $columns, "Mandatory columns should be the same");
    }

    /**
     * - Primary entity with no changes
     *
     * Expect:
     * - No exceptions
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testConstructor
     */
    public function testSave()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity = $this->primaryEntity();

        // Test
        try {
            $id = $entity->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        $this->assertEquals(self::PRIMARY_USER_ID, $id, "Primary user id should be returned");
    }

    /**
     * - Primary entity with no changes
     *
     * Expect:
     * - Values from the db match
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave
     */
    public function testSave_NoChanges()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $entity = $this->primaryEntity();

        // Test

        try {
            $entity->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify
        $dbEntity = userMgr()->findWithUsername(self::PRIMARY_USERNAME);
        $this->assertInstanceOf(UserEntity::class, $dbEntity, "\$dbEntity should be a UserEntity");
        $this->assertEquals(self::PRIMARY_USER_ID, $dbEntity->id, "User ID");
        $this->assertEquals(self::PRIMARY_USERNAME, $dbEntity->username, "Username");
        $this->assertEquals(self::PRIMARY_EMAIL, $dbEntity->email, "Email");
        $this->assertEquals(self::PRIMARY_ENABLED, $dbEntity->enabled, "User->enabled");
        $this->assertEquals(self::PRIMARY_THEME_ID, $dbEntity->themeId, "Theme ID");
// FIXME $this->assertTrue(false, "This assert is not implemented: Use ts to make sure it did not do the update statement");
    }

    /**
     * - Primary entity with changed username
     *
     * Expect:
     * - EntityInvalidSaveException
     * - Verify the exception message
     * - One invalid property (Username)
     * - Verify the invalid property message
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NoChanges
     */
    public function testSave_ChangeUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(EntityInvalidSaveException::class);

        $this->testResetDb();

        $exceptionMsg = "Cannot save the entity with these values";
        $invalidProperty = UserProperty::Username;
        $propertyMsgExpected = "It looks like you are trying to change the "
            . $invalidProperty->name . ". Currently, that feature is not available.";

        $newUsername = "Sue";
        $entity = $this->primaryEntity();
        $changed = new UserEntity(
            $entity->id,
            $newUsername,
            $entity->email,
            $entity->enabled,
            $entity->themeId);

        // Test

        try {
            $id = $changed->save();
        }
        catch (EntityInvalidSaveException $e) {

            // Verify

            $this->assertStringContainsString($exceptionMsg, $e->getMessage());
            $this->assertCount(1, $changed->invalidProperties(), "There should be 1 invalid property");
            $this->assertSame($invalidProperty, $changed->invalidProperties()[0], $invalidProperty->name . " should be an invalid property");
            $propertyMsg = $changed->invalidPropertyMessage($invalidProperty);
            $this->assertEquals($propertyMsgExpected, $propertyMsg, "Wrong error message for an invalid " . $invalidProperty->name);

            throw $e;

        }
    }

    /**
     * - Primary entity with changed email
     *
     * Expect:
     * - Values from the db match
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NoChanges
     */
    public function testSave_ChangeEmail()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->testResetDb();

        $newEmail = "sue@example.com";
        $entity = $this->primaryEntity();
        $changed = new UserEntity(
            $entity->id,
            $entity->username,
            $newEmail,
            $entity->enabled,
            $entity->themeId);

        // Test

        try {
            $changed->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify
        $dbEntity = userMgr()->findWithUsername(self::PRIMARY_USERNAME);
        $this->assertInstanceOf(UserEntity::class, $dbEntity, "\$dbEntity should be a UserEntity");
        $this->assertEquals(self::PRIMARY_USER_ID, $dbEntity->id, "User ID");
        $this->assertEquals(self::PRIMARY_USERNAME, $dbEntity->username, "Username");
        $this->assertEquals($newEmail, $dbEntity->email, "Email");
        $this->assertEquals(self::PRIMARY_ENABLED, $dbEntity->enabled, "User->enabled");
        $this->assertEquals(self::PRIMARY_THEME_ID, $dbEntity->themeId, "Theme ID");
    }

    /**
     * - Primary entity value of enabled changed
     *
     * Expect:
     * - Values from the db match
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NoChanges
     */
    public function testSave_ChangeEnabled()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->testResetDb();

        $newEnabled = !self::PRIMARY_ENABLED;
        $entity = $this->primaryEntity();
        $changed = new UserEntity(
            $entity->id,
            $entity->username,
            $entity->email,
            $newEnabled,
            $entity->themeId);

        // Test

        try {
            $changed->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify
        $dbEntity = userMgr()->findWithUsername(self::PRIMARY_USERNAME);
        $this->assertInstanceOf(UserEntity::class, $dbEntity, "\$dbEntity should be a UserEntity");
        $this->assertEquals(self::PRIMARY_USER_ID, $dbEntity->id, "User ID");
        $this->assertEquals(self::PRIMARY_USERNAME, $dbEntity->username, "Username");
        $this->assertEquals(self::PRIMARY_EMAIL, $dbEntity->email, "Email");
        $this->assertEquals($newEnabled, $dbEntity->enabled, "User->enabled");
        $this->assertEquals(self::PRIMARY_THEME_ID, $dbEntity->themeId, "Theme ID");
    }

    /**
     * - Primary entity with changed themeId
     *
     * Expect:
     * - Values from the db match
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NoChanges
     */
    public function testSave_ChangeThemeId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->testResetDb();

        $newThemeId = self::PRIMARY_THEME_ID + 1;
        $entity = $this->primaryEntity();
        $changed = new UserEntity(
            $entity->id,
            $entity->username,
            $entity->email,
            $entity->enabled,
            $newThemeId);

        // Test

        try {
            $changed->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify
        $dbEntity = userMgr()->findWithUsername(self::PRIMARY_USERNAME);
        $this->assertInstanceOf(UserEntity::class, $dbEntity, "\$dbEntity should be a UserEntity");
        $this->assertEquals(self::PRIMARY_USER_ID, $dbEntity->id, "User ID");
        $this->assertEquals(self::PRIMARY_USERNAME, $dbEntity->username, "Username");
        $this->assertEquals(self::PRIMARY_EMAIL, $dbEntity->email, "Email");
        $this->assertEquals(self::PRIMARY_ENABLED, $dbEntity->enabled, "User->enabled");
        $this->assertEquals($newThemeId, $dbEntity->themeId, "Theme ID");
    }

    /**
     * - Primary entity with all fields changed except ID
     *
     * Expect:
     * - Values from the db match
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NoChanges
     */
    public function testSave_ChangeAll()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->testResetDb();

        // $newUsername = "Sue"; Cannot change Username
        $newEmail = "sue@example.com";
        $newEnabled = !self::PRIMARY_ENABLED;
        $newThemeId = self::PRIMARY_THEME_ID + 1;

        $entity = $this->primaryEntity();
        $changed = new UserEntity(
            $entity->id,
            $entity->username,
            $newEmail,
            $newEnabled,
            $newThemeId);

        // Test

        try {
            $changed->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify
        $dbEntity = userMgr()->findWithUsername(self::PRIMARY_USERNAME);
        $this->assertInstanceOf(UserEntity::class, $dbEntity, "\$dbEntity should be a UserEntity");
        $this->assertEquals(self::PRIMARY_USER_ID, $dbEntity->id, "User ID");
        // $this->assertEquals($newUsername, $dbEntity->username, "Username"); Cannot change Username
        $this->assertEquals($newEmail, $dbEntity->email, "Email");
        $this->assertEquals($newEnabled, $dbEntity->enabled, "User->enabled");
        $this->assertEquals($newThemeId, $dbEntity->themeId, "Theme ID");
    }

    /**
     * - New user with valid properties
     *
     * Expect:
     * - No exceptions
     * - Get a new db id
     * - Values from the db match
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave
     */
    public function testSave_NewUser()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->testResetDb();

        $username = "Sue";
        $email = "sue@example.com";
        $enabled = true;
        $themeId = 1;

        $entity = new UserEntity(
            null,
            $username,
            $email,
            $enabled,
            $themeId);

        // Test

        try {
            $id = $entity->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify
        $dbEntity = userMgr()->findWithUsername($username);
        $this->assertInstanceOf(UserEntity::class, $dbEntity, "\$dbEntity should be a UserEntity");
        $this->assertEquals($id, $dbEntity->id, "User ID");
        $this->assertEquals($username, $dbEntity->username, "Username");
        $this->assertEquals($email, $dbEntity->email, "Email");
        $this->assertEquals($enabled, $dbEntity->enabled, "User->enabled");
        $this->assertEquals($themeId, $dbEntity->themeId, "Theme ID");
    }

    /**
     * - New user with valid properties, but defaults for all properties that
     *   have a default option.
     *
     * Expect:
     * - No exceptions
     * - Get a new db id
     * - Values from the db match
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NewUser
     */
    public function testSave_NewUserDefaults()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->testResetDb();

        $username = "Sue";
        $email = null;
        $enabled = true;
        $themeId = null;

        $entity = new UserEntity(
            null,
            $username,
            $email,
            $enabled,
            $themeId);

        // Test

        try {
            $id = $entity->save();
        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception in save(): " . $e->getMessage());
        }

        // Verify
        $dbEntity = userMgr()->findWithUsername($username);
        $this->assertInstanceOf(UserEntity::class, $dbEntity, "\$dbEntity should be a UserEntity");
        $this->assertEquals($id, $dbEntity->id, "User ID");
        $this->assertEquals($username, $dbEntity->username, "Username");
        $this->assertEquals($email, $dbEntity->email, "Email");
        $this->assertEquals($enabled, $dbEntity->enabled, "User->enabled");
        $this->assertEquals($themeId, $dbEntity->themeId, "Theme ID");
    }

    /**
     * - New entity with a username that was already taken
     *
     * Expect:
     * - EntityInvalidSaveException
     * - Verify the exception message
     * - One invalid property (Username)
     * - Verify the invalid property message
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NewUser
     */
    public function testSave_UsernameTaken()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(EntityInvalidSaveException::class);

        $this->testResetDb();

        $exceptionMsg = "Cannot save the entity with these values";
        $invalidProperty = UserProperty::Username;
        $username = self::PRIMARY_USERNAME;
        $propertyMsgExpected = $invalidProperty->name . " ($username) is already taken";

        $email = "sue@example.com";
        $enabled = true;
        $themeId = 1;

        $entity = new UserEntity(
            null,
            $username,
            $email,
            $enabled,
            $themeId);

        // Test

        try {
            $id = $entity->save();
        }
        catch (EntityInvalidSaveException $e) {

            // Verify

            $this->assertStringContainsString($exceptionMsg, $e->getMessage());
            $this->assertCount(1, $entity->invalidProperties(), "There should be 1 invalid property");
            $this->assertSame($invalidProperty, $entity->invalidProperties()[0], $invalidProperty->name . " should be an invalid property");
            $propertyMsg = $entity->invalidPropertyMessage($invalidProperty);
            $this->assertEquals($propertyMsgExpected, $propertyMsg, "Wrong error message for an invalid " . $invalidProperty->name);

            throw $e;

        }
    }

    /**
     * - New entity with a username that is too long
     *
     * Expect:
     * - EntityInvalidSaveException
     * - Verify the exception message
     * - One invalid property (Username)
     * - Verify the invalid property message
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NewUser
     */
    public function testSave_UsernameMax()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(EntityInvalidSaveException::class);

        $this->testResetDb();

        $exceptionMsg = "Cannot save the entity with these values";
        $invalidProperty = UserProperty::Username;
        $propertyMsgExpected = $invalidProperty->name . " max length is " . UserProperty::emailMax();

        $username = "1111111111_2222222222_3333333333_4444444444_5555555555";
        $email = "sue@example.com";
        $enabled = true;
        $themeId = 1;

        $entity = new UserEntity(
            null,
            $username,
            $email,
            $enabled,
            $themeId);

        // Test

        try {
            $id = $entity->save();
        }
        catch (EntityInvalidSaveException $e) {

            // Verify

            $this->assertStringContainsString($exceptionMsg, $e->getMessage());
            $this->assertCount(1, $entity->invalidProperties(), "There should be 1 invalid property");
            $this->assertSame($invalidProperty, $entity->invalidProperties()[0], $invalidProperty->name . " should be an invalid property");
            $propertyMsg = $entity->invalidPropertyMessage($invalidProperty);
            $this->assertEquals($propertyMsgExpected, $propertyMsg, "Wrong error message for an invalid " . $invalidProperty->name);

            throw $e;

        }
    }

    /**
     * - New entity with an email that is too long
     *
     * Expect:
     * - EntityInvalidSaveException
     * - Verify the exception message
     * - One invalid property (Email)
     * - Verify the invalid property message
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NewUser
     */
    public function testSave_EmailMax()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(EntityInvalidSaveException::class);

        $this->testResetDb();

        $exceptionMsg = "Cannot save the entity with these values";
        $invalidProperty = UserProperty::Email;
        $propertyMsgExpected = $invalidProperty->name . " max length is " . UserProperty::emailMax();

        $username = "Sue";
        $email = "s15_22222_3333333333_4444444444_5555555555@example.com";
        $enabled = true;
        $themeId = 1;

        $entity = new UserEntity(
            null,
            $username,
            $email,
            $enabled,
            $themeId);

        // Test

        try {
            $id = $entity->save();
        }
        catch (EntityInvalidSaveException $e) {

            // Verify

            $this->assertStringContainsString($exceptionMsg, $e->getMessage());
            $this->assertCount(1, $entity->invalidProperties(), "There should be 1 invalid property");
            $this->assertSame($invalidProperty, $entity->invalidProperties()[0], $invalidProperty->name . " should be an invalid property");
            $propertyMsg = $entity->invalidPropertyMessage($invalidProperty);
            $this->assertEquals($propertyMsgExpected, $propertyMsg, "Wrong error message for an invalid " . $invalidProperty->name);

            throw $e;

        }
    }

    /**
     * - New entity with a themeId that does not exist
     *
     * Expect:
     * - EntityInvalidSaveException
     * - Verify the exception message
     * - One invalid property (ThemeId)
     * - Verify the invalid property message
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NewUser
     */
    public function testSave_ThemeIdNonExistent()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(EntityInvalidSaveException::class);

        $this->testResetDb();

        $exceptionMsg = "Cannot save the entity with these values";
        $invalidProperty = UserProperty::ThemeId;
        $propertyMsgExpected = $invalidProperty->name . " does not match an active theme";

        $username = "Sue";
        $email = "sue@example.com";
        $enabled = true;
        $themeId = 1000;

        $entity = new UserEntity(
            null,
            $username,
            $email,
            $enabled,
            $themeId);

        // Test

        try {
            $id = $entity->save();
        }
        catch (EntityInvalidSaveException $e) {

            // Verify

            $this->assertStringContainsString($exceptionMsg, $e->getMessage());
            $this->assertCount(1, $entity->invalidProperties(), "There should be 1 invalid property");
            $this->assertSame($invalidProperty, $entity->invalidProperties()[0], $invalidProperty->name . " should be an invalid property");
            $propertyMsg = $entity->invalidPropertyMessage($invalidProperty);
            $this->assertEquals($propertyMsgExpected, $propertyMsg, "Wrong error message for an invalid " . $invalidProperty->name);

            throw $e;

        }
    }

    /**
     * - New entity with an inactive themeId
     *
     * Expect:
     * - EntityInvalidSaveException
     * - Verify the exception message
     * - One invalid property (ThemeId)
     * - Verify the invalid property message
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NewUser
     */
    public function testSave_ThemeIdInactive()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(EntityInvalidSaveException::class);

        $this->testResetDb();

        $exceptionMsg = "Cannot save the entity with these values";
        $invalidProperty = UserProperty::ThemeId;
        $propertyMsgExpected = $invalidProperty->name . " does not match an active theme";

        $username = "Sue";
        $email = "sue@example.com";
        $enabled = true;
        $themeId = 2;

        $entity = new UserEntity(
            null,
            $username,
            $email,
            $enabled,
            $themeId);

        $db = getDatabase();
        $db->exec("UPDATE theme SET enabled=false WHERE id=2");

        // Test

        try {
            $id = $entity->save();
        }
        catch (EntityInvalidSaveException $e) {

            // Verify

            $this->assertStringContainsString($exceptionMsg, $e->getMessage());
            $this->assertCount(1, $entity->invalidProperties(), "There should be 1 invalid property");
            $this->assertSame($invalidProperty, $entity->invalidProperties()[0], $invalidProperty->name . " should be an invalid property");
            $propertyMsg = $entity->invalidPropertyMessage($invalidProperty);
            $this->assertEquals($propertyMsgExpected, $propertyMsg, "Wrong error message for an invalid " . $invalidProperty->name);

            throw $e;

        }
    }

    /**
     * - Try to change a user with an Id that is not in the db
     *
     * Expect:
     * - EntityInvalidSaveException
     * - Verify the exception message
     * - One invalid property (Id)
     * - Verify the invalid property message
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testSave_NewUser
     */
    public function testSave_IdNotFound()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup

        $this->expectException(EntityInvalidSaveException::class);

        $this->testResetDb();

        $exceptionMsg = "Cannot save the entity with these values";
        $invalidProperty = UserProperty::Id;
        $id = 1000;
        $propertyMsgExpected = $invalidProperty->name . " " . $id . " not found";

        $username = self::PRIMARY_USERNAME;
        $email = self::PRIMARY_EMAIL;
        $enabled = self::PRIMARY_ENABLED;
        $themeId = self::PRIMARY_THEME_ID;

        $entity = new UserEntity(
            $id,
            $username,
            $email,
            $enabled,
            $themeId);

        // Test

        try {
            $entity->save();
        }
        catch (EntityInvalidSaveException $e) {

            // Verify

            $this->assertStringContainsString($exceptionMsg, $e->getMessage());
            $this->assertCount(1, $entity->invalidProperties(), "There should be 1 invalid property");
            $this->assertSame($invalidProperty, $entity->invalidProperties()[0], $invalidProperty->name . " should be an invalid property");
            $propertyMsg = $entity->invalidPropertyMessage($invalidProperty);
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
     * @covers \RatingSync\UserEntity::save
     * @depends testConstructor
     */
    public function testEquals()
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
     * @covers \RatingSync\UserEntity::save
     * @depends testEquals
     */
    public function testEquals_DiffId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 = $this->primaryEntity();
        $entity2 = new UserEntity(
            1000,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            self::PRIMARY_THEME_ID
        );

        // Test
        $equals = $entity1->equals($entity2);

        $this->assertFalse($equals, "The 2 entities should not be equals");
    }

    /**
     * - 2 entities has one property different: username
     *
     * Expect:
     * - Return false
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testEquals
     */
    public function testEquals_DiffUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 = $this->primaryEntity();
        $entity2 = new UserEntity(
            self::PRIMARY_USER_ID,
            "Sue",
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            self::PRIMARY_THEME_ID
        );

        // Test
        $equals = $entity1->equals($entity2);

        $this->assertFalse($equals, "The 2 entities should not be equals");
    }

    /**
     * - 2 entities has one property different: email
     *
     * Expect:
     * - Return false
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testEquals
     */
    public function testEquals_DiffEmail()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 = $this->primaryEntity();
        $entity2 = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            "sue@example.com",
            self::PRIMARY_ENABLED,
            self::PRIMARY_THEME_ID
        );

        // Test
        $equals = $entity1->equals($entity2);

        $this->assertFalse($equals, "The 2 entities should not be equals");
    }

    /**
     * - 2 entities has one property different: one email is null
     *
     * Expect:
     * - Return false
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testEquals
     */
    public function testEquals_DiffEmailNull()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 = $this->primaryEntity();
        $entity2 = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            null,
            self::PRIMARY_ENABLED,
            self::PRIMARY_THEME_ID
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
     * @covers \RatingSync\UserEntity::save
     * @depends testEquals
     */
    public function testEquals_DiffEnabled()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 = $this->primaryEntity();
        $entity2 = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            !self::PRIMARY_ENABLED,
            self::PRIMARY_THEME_ID
        );

        // Test
        $equals = $entity1->equals($entity2);

        $this->assertFalse($equals, "The 2 entities should not be equals");
    }

    /**
     * - 2 entities has one property different: themeId
     *
     * Expect:
     * - Return false
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testEquals
     */
    public function testEquals_DiffThemeId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 =
        $entity1 = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            1
        );
        $entity2 = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            2
        );

        // Test
        $equals = $entity1->equals($entity2);

        $this->assertFalse($equals, "The 2 entities should not be equals");
    }

    /**
     * - 2 entities has one property different: one themeId is null
     *
     * Expect:
     * - Return false
     *
     * @covers \RatingSync\UserEntity::save
     * @depends testEquals
     */
    public function testEquals_DiffThemeIdNull()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity1 = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            1
        );
        $entity2 = new UserEntity(
            self::PRIMARY_USER_ID,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            null
        );

        // Test
        $equals = $entity1->equals($entity2);

        $this->assertFalse($equals, "The 2 entities should not be equals");
    }

}
