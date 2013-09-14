<?php

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-12-01 at 14:50:59.
 */
class BMDieTest extends PHPUnit_Framework_TestCase {

    /**
     * @var BMDie
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new BMDie;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    public function testAdd_skill() {
        // Check that the skill list is indeed empty
        $sl = PHPUnit_Framework_Assert::readAttribute($this->object, "skillList");
        $hl = PHPUnit_Framework_Assert::readAttribute($this->object, "hookList");

        $this->assertEmpty($sl, "Skill list not initially empty.");
        $this->assertFalse(array_key_exists("test", $hl), "Hook list not initially empty.");

        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");

        $sl = PHPUnit_Framework_Assert::readAttribute($this->object, "skillList");
        $this->assertNotEmpty($sl, "Skill list should not be empty.");
        $this->assertEquals(count($sl), 1, "Skill list contains more than it should.");
        $this->assertArrayHasKey('Testing', $sl, "Skill list doesn't contain 'Testing'");
        $this->assertEquals($sl["Testing"], "TestDummyBMSkillTesting", "Incorrect stored classname for 'Testing'");

        // Proper maintenance of the hook lists
        $hl = PHPUnit_Framework_Assert::readAttribute($this->object, "hookList");
        $this->assertArrayHasKey("test", $hl, "Hook list missing test hooks.");

        $this->assertContains("TestDummyBMSkillTesting", $hl["test"], "Hook list missing 'Testing' hook.");

        $this->assertEquals(1, count($hl), "Hook list contains something extra.");
        $this->assertEquals(1, count($hl["test"]), "Hook list for function 'test' contains something extra.");



        // Another skill

        $this->object->add_skill("Testing2", "TestDummyBMSkillTesting2");

        $sl = PHPUnit_Framework_Assert::readAttribute($this->object, "skillList");
        $this->assertNotEmpty($sl, "Skill list should not be empty.");
        $this->assertEquals(count($sl), 2, "Skill list contains more than it should.");
        $this->assertArrayHasKey('Testing', $sl, "Skill list doesn't contain 'Testing'");
        $this->assertArrayHasKey('Testing2', $sl, "Skill list doesn't contain 'Testing2'");
        $this->assertEquals($sl["Testing2"], "TestDummyBMSkillTesting2", "Incorrect stored classname for 'Testing2'");


        // Redundancy

        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");

        $sl = PHPUnit_Framework_Assert::readAttribute($this->object, "skillList");
        $this->assertEquals(count($sl), 2, "Skill list contains more than it should.");
        $this->assertArrayHasKey('Testing', $sl, "Skill list doesn't contain 'Testing'");
        $this->assertArrayHasKey('Testing2', $sl, "Skill list doesn't contain 'Testing2'");

        // Proper maintenance of the hook lists
        $hl = PHPUnit_Framework_Assert::readAttribute($this->object, "hookList");
        $this->assertArrayHasKey("test", $hl, "Hook list missing test hooks.");

        $this->assertContains("TestDummyBMSkillTesting", $hl["test"], "Hook list missing 'Testing' hook.");
        $this->assertContains("TestDummyBMSkillTesting2", $hl["test"], "Hook list missing 'Testing2' hook.");

        $this->assertEquals(1, count($hl), "Hook list contains something extra.");
        $this->assertEquals(2, count($hl["test"]), "Hook list for function 'test' contains something extra.");



    }

    /**
     * @depends testAdd_skill
     */
    public function testHas_skill() {
        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");
        $this->object->add_skill("Testing2", "TestDummyBMSkillTesting2");
        $this->assertTrue($this->object->has_skill("Testing"));
        $this->assertTrue($this->object->has_skill("Testing2"));
        $this->assertFalse($this->object->has_skill("Testing3"));
    }

    /**
     * @depends testAdd_skill
     * @depends testHas_skill
     */
    public function testRemove_skill() {

        // simple
        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");
        $this->assertTrue($this->object->remove_skill("Testing"));
        $this->assertFalse($this->object->has_skill("Testing"));

        // multiple skills
        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");
        $this->object->add_skill("Testing2", "TestDummyBMSkillTesting2");
        $this->assertTrue($this->object->remove_skill("Testing"));
        $this->assertFalse($this->object->has_skill("Testing"));
        $this->assertTrue($this->object->has_skill("Testing2"));

        // fail to remove non-existent skills
        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");
        $this->assertFalse($this->object->remove_skill("Testing3"));
        $this->assertTrue($this->object->has_skill("Testing"));
        $this->assertTrue($this->object->has_skill("Testing2"));

        // examine the hook list for proper editing
        $this->assertTrue($this->object->remove_skill("Testing2"));
        $this->assertTrue($this->object->has_skill("Testing"));
        $this->assertFalse($this->object->has_skill("Testing2"));

        $hl = PHPUnit_Framework_Assert::readAttribute($this->object, "hookList");
        $this->assertArrayHasKey("test", $hl, "Hook list missing test hooks.");

        $this->assertContains("TestDummyBMSkillTesting", $hl["test"], "Hook list missing 'Testing' hook.");
        $this->assertNotContains("TestDummyBMSkillTesting2", $hl["test"], "Hook list _not_ missing 'Testing2' hook.");

        $this->assertEquals(1, count($hl), "Hook list contains something extra.");
        $this->assertEquals(1, count($hl["test"]), "Hook list for function 'test' contains something extra.");
    }

    /**
     * @depends testAdd_skill
     * @depends testHas_skill
     * @depends testRemove_skill
     */
    public function testRun_hooks() {
        $die = new TestDummyBMDieTesting;

        $die->add_skill("Testing", "TestDummyBMSkillTesting");

        $die->test();

        $this->assertEquals("testing", $die->testvar);

        $die->remove_skill("Testing");
        $die->add_skill("Testing2", "TestDummyBMSkillTesting2");

        $die->test();
        $this->assertEquals("still testing", $die->testvar);

        $die->add_skill("Testing", "TestDummyBMSkillTesting");

        $die->test();
        // order in which hooks run is not guaranteed
        $this->assertRegExp('/testingstill testing|still testingtesting/', $die->testvar);
    }


    /**
     * @depends testAdd_skill
     * @depends testHas_skill
     * @depends testRemove_skill
     */
    public function testInit() {
        $this->object->init(6, array("TestDummyBMSkillTesting" => "Testing"));

        $this->assertEquals($this->object->min, 1);
        $this->assertEquals($this->object->max, 6);

        $this->assertTrue($this->object->has_skill("Testing"));

        $this->object->init(14, array("TestDummyBMSkillTesting2" => "Testing2"));

        $this->assertEquals($this->object->min, 1);
        $this->assertEquals($this->object->max, 14);

        $this->assertTrue($this->object->has_skill("Testing2"));

        // init does not remove old skills, or otherwise reset variables
        // at the moment. It's for working on brand-new dice
        $this->assertTrue($this->object->has_skill("Testing"));
    }

    /**
     * @depends testInit
     */
    public function testCreate() {
        $die = BMDie::create(6, array());

        $this->assertInstanceOf('BMDie', $die);
        $this->assertEquals(6, $die->max);

        // expectedException aborts function execution when the
        // exception is thrown, so doesn't work as part of a large
        // blob of tests.

        $fail = FALSE;

        try {
            $die = BMDie::create(-15, array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating out-of-range die didn't throw an exception.");

        $this->assertEquals(6, $die->max);
        $fail = FALSE;

        // try some more bad values
        try {
            $die = BMDie::create(1023, array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }
        $this->assertTrue($fail, "Creating out-of-range die didn't throw an exception.");
        $fail = FALSE;

        try {
            $die = BMDie::create(0, array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating out-of-range die didn't throw an exception.");
        $fail = FALSE;

        try {
            $die = BMDie::create(100, array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating out-of-range die didn't throw an exception.");
        $fail = FALSE;

        // downright illegal values
        try {
            $die = BMDie::create("thing", array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating non-numeric die didn't throw an exception.");
        $fail = FALSE;

        try {
            $die = BMDie::create("4score", array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating non-numeric die didn't throw an exception.");
        $fail = FALSE;

        try {
            $die = BMDie::create(2.718, array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating non-numeric die didn't throw an exception.");
        $fail = FALSE;

        try {
            $die = BMDie::create("thing8", array());
        }
        catch (UnexpectedValueException $e) {
            $fail = TRUE;
        }

        $this->assertTrue($fail, "Creating non-numeric die didn't throw an exception.");
    }

    /**
     * @depends testCreate
     */
    public function testCreate_from_string() {
        // We only test creation of standard die types here.
        // (and errors)
        //
        // The complex types can work this function out in their own
        // test suites

        $die = BMDie::create_from_string("72", array());
        $this->assertInstanceOf('BMDie', $die);
        $this->assertEquals(72, $die->max);

        $die = BMDie::create_from_string("himom!", array());
        $this->assertNull($die);

        $die = BMDie::create_from_string("75.3", array());
        $this->assertNull($die);

        $die = BMDie::create_from_string("trombones76", array());
        $this->assertNull($die);

        $die = BMDie::create_from_string("76trombones", array());
        $this->assertNull($die);

    }

    /**
     * @covers BMDie::activate
     */

    public function testActivate() {
        $game = new TestDummyGame;
        $this->object->ownerObject = $game;
        $this->object->activate('player');
        $newDie = $game->dice[0][1];

        $this->assertInstanceOf('BMDie', $newDie);

        $this->assertTrue($game === $newDie->ownerObject);

        // Make the dice equal in value

        $this->assertFalse(($this->object === $newDie), "activate returned the same object.");
    }

    /**
     * @coversNothing
     */
    public function testIntegrationActivate() {
        $game = new BMGame;
        $game->activeDieArrayArray = array(array(), array());
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 1;
        $this->object->activate();
        $this->assertInstanceOf('BMDie', $game->activeDieArrayArray[1][0]);
    }

    /**
     * @depends testInit
     */
    public function testRoll() {
        $this->object->init(6, array());

        for($i = 1; $i <= 6; $i++) {
            $rolls[$i] = 0;
        }

        for ($i = 0; $i < 300; $i++) {
            $this->object->roll(FALSE);
            if ($this->object->value < 1 || $this->object->value > 6) {
                $this->assertFalse(TRUE, "Die rolled out of bounds during FALSE.");
            }

            $rolls[$this->object->value]++;
        }

        for ($i = 0; $i < 300; $i++) {
            $this->object->roll(TRUE);
            if ($this->object->value < 1 || $this->object->value > 6) {
                $this->assertFalse(TRUE, "Die rolled out of bounds during TRUE.");
            }

            $rolls[$this->object->value]++;
        }

        // How's our randomness?
        //
        // We're only testing for "terrible" here.
        for($i = 1; $i <= 6; $i++) {
            $this->assertGreaterThan(25, $rolls[$i], "randomness dubious for $i");
            $this->assertLessThan(175, $rolls[$i], "randomness dubious for $i");
        }

        // test locked-out rerolls

        $val = $this->object->value;

        $this->object->doesReroll = FALSE;

        for ($i = 0; $i<20; $i++) {
            // Test both on successful attack and not
            $this->object->roll($i % 2);
            $this->assertEquals($val, $this->object->value, "Die value changed.");
        }
    }

    /**
     * @depends testRoll
     * @depends testInit
     */
    public function testMake_play_die() {
        $this->object->init(6, array());

        $newDie = $this->object->make_play_die();

        $this->assertInstanceOf('BMDie', $newDie);

        $this->assertGreaterThanOrEqual(1, $newDie->value);
        $this->assertLessThanOrEqual(6, $newDie->value);

        // Make the dice equal in value

        $this->object->value = $newDie->value;

        $this->assertFalse(($this->object === $newDie), "make_play_die returned the same object.");
    }

    public function testAttack_list() {
        $this->assertNotEmpty($this->object->attack_list());
        $this->assertContains("Skill", $this->object->attack_list());
        $this->assertContains("Power", $this->object->attack_list());
        $this->assertNotEmpty($this->object->attack_list());
        $this->assertEquals(2, count($this->object->attack_list()));
    }

    /**
     * @depends testInit
     * @depends testAttack_list
     */
    public function testAttack_values() {
        $this->object->value = 7;

        foreach ($this->object->attack_list() as $att) {
            $this->assertNotEmpty($this->object->attack_values($att));
            $this->assertContains(7, $this->object->attack_values($att));
            $this->assertEquals(1, count($this->object->attack_values($att)));
        }

        $this->assertNotEmpty($this->object->attack_values("Bob"));
        $this->assertContains(7, $this->object->attack_values("Bob"));
        $this->assertEquals(1, count($this->object->attack_values("Bob")));

        $this->object->value = 4;
        foreach ($this->object->attack_list() as $att) {
            $this->assertNotEmpty($this->object->attack_values($att));
            $this->assertContains(4, $this->object->attack_values($att));
            $this->assertEquals(1, count($this->object->attack_values($att)));
        }


    }

    /**
     * @depends testInit
     * @depends testRoll
     * @depends testAttack_list
     */
    public function testDefense_value() {
        $this->object->init(6, array());

        foreach ($this->object->attack_list() as $att) {
            for ($i = 0; $i<10; $i++) {
                $this->object->roll(FALSE);
                $this->assertEquals($this->object->value, $this->object->defense_value($att), "Defense value fails to equal value for $att.");
            }
        }

    }

    /**
     * @depends testInit
     */
    public function testGet_scoreValueTimesTen() {
        $this->object->init(7, array());

        $this->assertEquals(35, $this->object->get_scoreValueTimesTen());

        $this->object->captured = TRUE;

        $this->assertEquals(70, $this->object->get_scoreValueTimesTen());

    }


    /**
     * @depends testInit
     * @depends testRoll
     */
    public function testInitiative_value() {
        $this->object->init(6, array());
        $this->object->roll(FALSE);

        $vals = $this->object->initiative_value();

        $this->assertNotEmpty($vals);
        $this->assertEquals(1, count($vals));

        $this->assertEquals($vals[0], $this->object->value);

    }

    /**
     * @depends testAttack_list
     */
    public function testAssist_values() {
        $attDie = new BMDie;
        $defDie = new BMDie;

        foreach ($this->object->attack_list() as $att) {
            $assistVals = $this->object->assist_values($att,
                                                       array($attDie),
                                                       array($defDie));
            $this->assertNotEmpty($assistVals);
            $this->assertEquals(1, count($assistVals));
            $this->assertEquals(0, $assistVals[0]);
        }

        // test that we don't assist attacks we are making
        $this->object->add_skill("AVTesting", "TestDummyBMSkillAVTesting");

        // test that the assist skill works
        $assistVals = $this->object->assist_values($att,
                                                   array($attDie),
                                                   array($defDie));
        $this->assertNotEmpty($assistVals);
        $this->assertEquals(2, count($assistVals));
        $this->assertEquals(-1, $assistVals[0]);
        $this->assertEquals(1, $assistVals[1]);

        // now make it not work
        $assistVals = $this->object->assist_values($att,
                                                   array($this->object),
                                                   array($defDie));
        $this->assertNotEmpty($assistVals);
        $this->assertEquals(1, count($assistVals));
        $this->assertEquals(0, $assistVals[0]);

        $assistVals = $this->object->assist_values($att,
                                                   array($attDie, $this->object),
                                                   array($defDie));
        $this->assertNotEmpty($assistVals);
        $this->assertEquals(1, count($assistVals));
        $this->assertEquals(0, $assistVals[0]);
    }

    /**
     * @depends testAttack_list
     * @depends testAssist_values
     */
    public function testAttack_contribute() {
        $attDie = new BMDie;
        $defDie = new BMDie;

        foreach ($this->object->attack_list() as $att) {
            $this->assertFalse($this->object->attack_contribute($att,
                                                                array($attDie),
                                                                array($defDie),
                                                                1));
            $this->assertFalse($this->object->attack_contribute($att,
                                                                array($attDie),
                                                                array($defDie),
                                                                0));
        }
    }


    /**
     * @depends testAttack_list
     */
    public function testValid_attack() {
        $attDie = new BMDie;
        $defDie = new BMDie;

        foreach ($this->object->attack_list() as $att) {

            $this->assertFalse($this->object->valid_attack($att,
                                                           array($attDie),
                                                           array($defDie)));
            $this->assertTrue($this->object->valid_attack($att,
                                                          array($this->object),
                                                          array($defDie)));
            $this->assertFalse($this->object->valid_attack($att,
                                                           array($attDie),
                                                           array($this->object)));
            $this->assertTrue($this->object->valid_attack($att,
                                                          array($this->object, $attDie),
                                                          array($defDie)));
        }

        // Inactive is a string also used to descrbe why the die cannot attack
        $this->object->inactive = "Yes";
        $this->assertFalse($this->object->valid_attack($att,
                                                       array($this->object),
                                                       array($defDie)));

        $this->object->inactive = "";
        $this->object->hasAttacked = TRUE;
        $this->assertFalse($this->object->valid_attack($att,
                                                       array($this->object),
                                                       array($defDie)));


        $this->object->inactive = "Yes";
        $this->object->hasAttacked = TRUE;
        $this->assertFalse($this->object->valid_attack($att,
                                                       array($this->object),
                                                       array($defDie)));

    }

    /**
     * @depends testAttack_list
     */
    public function testValid_target() {
        $attDie = new BMDie;
        $defDie = new BMDie;

        foreach ($this->object->attack_list() as $att) {

            $this->assertFalse($this->object->valid_target($att,
                                                           array($attDie),
                                                           array($defDie)));
            $this->assertFalse($this->object->valid_target($att,
                                                          array($this->object),
                                                          array($defDie)));
            $this->assertTrue($this->object->valid_target($att,
                                                           array($attDie),
                                                           array($this->object)));
            $this->assertTrue($this->object->valid_target($att,
                                                          array($attDie),
                                                          array($this->object, $defDie)));
        }

        $this->object->unavailable = TRUE;
        $this->assertFalse($this->object->valid_target($att,
                                                       array($attDie),
                                                       array($this->object)));

    }

    /**
     * @depends testAttack_list
     */
    public function testCapture() {
        // How does one test a function that doesn't do anything, but
        // exists solely to be modified?
        $defDie = new BMDie;

        foreach ($this->object->attack_list() as $att) {
            $this->object->capture($att, array($this->object), array($defDie));
        }
    }

    /**
     * @depends testAttack_list
     */
    public function testBe_captured() {
        $attDie = new BMDie;

        $this->assertFalse($this->object->captured);

        foreach ($this->object->attack_list() as $att) {
            $this->object->be_captured($att, array($attDie), array($this->object));
            $this->assertTrue($this->object->captured);

            $this->object->captured = FALSE;
        }
    }

    public function testDescribe() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @depends testInit
     * @depends testRoll
     */
    public function testSplit() {
        // 1-siders split into two 1-siders
        $this->object->init(1, array());
        $this->object->roll(FALSE);

        $dice = $this->object->split();

        $this->assertFalse($dice[0] === $dice[1]);
        $this->assertTrue($this->object === $dice[0]);
        $this->assertEquals($dice[0]->max, $dice[1]->max);
        $this->assertEquals(1, $dice[0]->max);

        // even-sided split
        $this->object->init(12, array());
        $this->object->roll(FALSE);

        $dice = $this->object->split();

        $this->assertFalse($dice[0] === $dice[1]);
        $this->assertTrue($this->object === $dice[0]);
        $this->assertEquals($dice[0]->max, $dice[1]->max);
        $this->assertEquals(6, $dice[0]->max);

        // odd-sided split
        $this->object->init(7, array());
        $this->object->roll(FALSE);

        $dice = $this->object->split();

        $this->assertFalse($dice[0] === $dice[1]);
        $this->assertTrue($this->object === $dice[0]);
        $this->assertNotEquals($dice[0]->max, $dice[1]->max);

        // The order of arguments for assertGreaterThan is screwy.
        $this->assertGreaterThan($dice[1]->max, $dice[0]->max);
        $this->assertEquals(4, $dice[0]->max);
        $this->assertEquals(3, $dice[1]->max);

    }

    public function testRun_hooks_at_game_state() {
        $this->object->playerIdx = 0;

        $this->assertEquals("", $this->object->inactive);
        $this->assertFalse($this->object->hasAttacked);

        $this->object->run_hooks_at_game_state(BMGameState::endTurn, 0);

        $this->assertEquals("", $this->object->inactive);
        $this->assertFalse($this->object->hasAttacked);

        $this->hasAttacked = TRUE;
        $this->object->run_hooks_at_game_state(BMGameState::endTurn, 0);
        $this->assertFalse($this->object->hasAttacked);

        $this->hasAttacked = TRUE;
        $this->object->run_hooks_at_game_state(BMGameState::endTurn, 1);
        $this->assertFalse($this->object->hasAttacked);

        $this->object->inactive = "Yes";
        $this->object->run_hooks_at_game_state(BMGameState::endTurn, 1);
        $this->assertNotEquals("", $this->object->inactive);
        $this->object->run_hooks_at_game_state(BMGameState::endTurn, 0);
        $this->assertEquals("", $this->object->inactive);

        $this->hasAttacked = TRUE;
        $this->object->inactive = "Yes";
        $this->object->run_hooks_at_game_state(BMGameState::endTurn, 1);
        $this->assertFalse($this->object->hasAttacked);
        $this->assertNotEquals("", $this->object->inactive);

        $this->hasAttacked = TRUE;
        $this->object->inactive = "Yes";
        $this->object->run_hooks_at_game_state(BMGameState::endTurn, 0);
        $this->assertFalse($this->object->hasAttacked);
        $this->assertEquals("", $this->object->inactive);
    }

    public function test__get() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    public function test__set() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    public function test__toString() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    public function test__clone() {
        // Doesn't do anything at the moment.
        $this->assertTrue(TRUE);
    }

}


