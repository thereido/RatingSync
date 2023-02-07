<?php

namespace RatingSync;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "EntityFactories" . DIRECTORY_SEPARATOR . "UserFactory.php";
require_once "DatabaseTest.php";
require_once "RatingSyncTestCase.php";

use Exception;
use \PDO;

class UserFactoryTest extends RatingSyncTestCase
{

    const PRIMARY_USER_ID = 1;
    const PRIMARY_USERNAME = "ratingsynctest";
    const PRIMARY_EMAIL = "testratingsync@example.com";
    const PRIMARY_ENABLED = true;
    const PRIMARY_THEME_ID = NULL;

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
     * @depends testResetDb
     */
    public function testConstruct()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $entity = $this->primaryEntity();
        $view = new UserView( $entity );

        // Test
        $factory = new UserFactory( $view );

        // Verify
        $this->assertInstanceOf(UserFactory::class, $factory, "\$factory should be a UserFactory");
    }

    /**
     * @depends testConstruct
     */
    public function testConstruct_WithWrongClass()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(\TypeError::class);

        // Setup
        $entity = $this->primaryEntity();

        // Test
        $factory = new UserFactory( $entity );
    }

    public function testEquals()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $primaryEntity = $this->primaryEntity();
        $otherEntity = $this->primaryEntity();

        // Verify
        $this->assertTrue($primaryEntity->equals($otherEntity), "Other entity should be the same as the primary entity");
    }

    /**
     * @depends testEquals
     */
    public function testEquals_AllFields()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $lhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                2);
            $rhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                2);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        // Verify
        $this->assertTrue($lhs->equals($rhs), "Left side entity should be the same as the right side entity");
    }

    /**
     * @depends testEquals
     */
    public function testEquals_NullFields()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $lhs = new UserEntity(
                1,
                "bob",
                NULL,
                false,
                NULL);
            $rhs = new UserEntity(
                1,
                "bob",
                NULL,
                false,
                NULL);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        // Verify
        $this->assertTrue($lhs->equals($rhs), "Left side entity should be the same as the right side entity");
    }

    /**
     * @depends testEquals_AllFields
     */
    public function testEquals_DiffId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $lhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                2);
            $rhs = new UserEntity(
                2,
                "bob",
                "bob@example.com",
                false,
                2);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        // Verify
        $this->assertFalse($lhs->equals($rhs), "Left side entity should be the same as the right side entity");
    }

    /**
     * @depends testEquals_DiffId
     */
    public function testEquals_DiffUsername()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $lhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                2);
            $rhs = new UserEntity(
                1,
                "sue",
                "bob@example.com",
                false,
                2);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        // Verify
        $this->assertFalse($lhs->equals($rhs), "Left side entity should be the same as the right side entity");
    }

    /**
     * @depends testEquals_DiffId
     */
    public function testEquals_DiffEmail()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $lhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                2);
            $rhs = new UserEntity(
                1,
                "bob",
                "sue@example.com",
                false,
                2);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        // Verify
        $this->assertFalse($lhs->equals($rhs), "Left side entity should be the same as the right side entity");
    }

    /**
     * @depends testEquals_DiffId
     */
    public function testEquals_DiffEnabled()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $lhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                2);
            $rhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                true,
                2);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        // Verify
        $this->assertFalse($lhs->equals($rhs), "Left side entity should be the same as the right side entity");
    }

    /**
     * @depends testEquals_DiffId
     */
    public function testEquals_DiffThemeId()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $lhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                2);
            $rhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                1);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        // Verify
        $this->assertFalse($lhs->equals($rhs), "Left side entity should be the same as the right side entity");
    }

    /**
     * @depends testEquals_AllFields
     * @depends testEquals_DiffId
     */
    public function testEquals_DiffMultipleFields()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $lhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                2);
            $rhs = new UserEntity(
                1,
                "sue", // Different
                "bob@example.com",
                true, // Different
                2);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        // Verify
        $this->assertFalse($lhs->equals($rhs), "Left side entity should be the same as the right side entity");
    }

    /**
     * @depends testEquals_DiffId
     */
    public function testEquals_CompareNullAndInt()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $lhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                2);
            $rhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                NULL);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        // Verify
        $this->assertFalse($lhs->equals($rhs), "Left side entity should be the same as the right side entity");
    }

    /**
     * @depends testEquals_DiffId
     */
    public function testEquals_CompareNullAndString()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $lhs = new UserEntity(
                1,
                "bob",
                "bob@example.com",
                false,
                2);
            $rhs = new UserEntity(
                1,
                "bob",
                NULL,
                false,
                2);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        // Verify
        $this->assertFalse($lhs->equals($rhs), "Left side entity should be the same as the right side entity");
    }

    /**
     * @depends testConstruct
     */
    public function testBuild()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        $primaryEntity = $this->primaryEntity();
        $view = new UserView( $primaryEntity );
        $factory = new UserFactory( $view );

        // Test
        $builtEntity = $factory->build();

        // Verify
        $this->assertInstanceOf(UserEntity::class, $builtEntity, "\$builtEntity should be a UserEntity");
        $this->assertTrue($primaryEntity->equals($builtEntity), "\$builtEntity should be the same as the primary entity");
    }

    /**
     * @depends testBuild
     */
    public function testBuild_ForNewUser()
    {$this->start(__CLASS__, __FUNCTION__);

        // Setup
        try {

            $entity = new UserEntity(
                -1,
                self::PRIMARY_USERNAME,
                self::PRIMARY_EMAIL,
                self::PRIMARY_ENABLED,
                self::PRIMARY_THEME_ID);

        }
        catch (Exception $e) {
            $this->assertTrue(false, "Exception constructing UserEntities.\n" . $e->getMessage());
        }

        $view = new UserView( $entity );
        $factory = new UserFactory( $view );

        // Test
        $builtEntity = $factory->build();

        // Verify
        $this->assertInstanceOf(UserEntity::class, $builtEntity, "\$builtEntity should be a UserEntity");
        $this->assertTrue($entity->equals($builtEntity), "\$builtEntity should be the same as the original entity");
    }

    /**
     * @depends testBuild
     */
    public function testBuild_InvalidId()
    {$this->start(__CLASS__, __FUNCTION__);

        $this->expectException(Exception::class);

        // Setup
        $entity = new UserEntity(
            -2,
            self::PRIMARY_USERNAME,
            self::PRIMARY_EMAIL,
            self::PRIMARY_ENABLED,
            self::PRIMARY_THEME_ID);

        // Test
        //$factory->build(); // UserEntity construction will throw an exception before this
    }

}
