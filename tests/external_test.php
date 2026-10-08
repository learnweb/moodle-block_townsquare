<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for the block_townsquare.
 *
 * @package   block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_townsquare;

use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * PHPUnit tests for testing the process of the externallib.
 *
 * @package   block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_townsquare\external\record_usersettings
 * @covers \block_townsquare\external\reset_usersettings
 * @runTestsInSeparateProcesses
 */
final class external_test extends \advanced_testcase {
    /** @var object That that is used for testing
     * It contains an instance of the townsquare external class.
     *
     */
    private $testdata;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->testdata = new stdClass();
    }

    /**
     * Tests the record_usersettings method.
     * @runInSeparateProcess
     */
    public function test_record_usersettings(): void {
        global $DB;
        $this->testdata->external = new external\record_usersettings();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $usersetting = new stdClass();
        $usersetting->userid = $user->id;
        $usersetting->timefilterpast = 432000;
        $usersetting->timefilterfuture = 2592000;
        $usersetting->basicletter = 0;
        $usersetting->completionletter = 1;
        $usersetting->postletter = 1;
        $usersetting->courses = json_encode((object) ["2" => true, "4" => false]);

        // Test Case 1: The User sets the setting for the first time.

        // Check that there is no record in the database.
        $record = $DB->get_record('block_townsquare_preferences', ['userid' => $usersetting->userid]);
        $this->assertEquals(false, $record);

        // Call the function to record the user settings and check, if the record is created.
        $result = $this->testdata->external->execute(
            $usersetting->timefilterpast,
            $usersetting->timefilterfuture,
            $usersetting->basicletter,
            $usersetting->completionletter,
            $usersetting->postletter,
            $usersetting->courses
        );

        $this->assertEquals(true, $result);
        $record = $DB->get_record('block_townsquare_preferences', ['userid' => $usersetting->userid]);

        // Check if the record is correct.
        $this->assertEquals($usersetting->timefilterpast, $record->timefilterpast);
        $this->assertEquals($usersetting->timefilterfuture, $record->timefilterfuture);
        $this->assertEquals($usersetting->basicletter, $record->basicletter);
        $this->assertEquals($usersetting->completionletter, $record->completionletter);
        $this->assertEquals($usersetting->postletter, $record->postletter);
        $this->assertEquals($usersetting->courses, $record->courses);

        // Test Case 2: The User updates the settings.
        $usersetting->timefilterpast = 2592000;
        $usersetting->timefilterfuture = 0;
        $usersetting->basicletter = 1;
        $usersetting->completionletter = 0;
        $usersetting->postletter = 0;
        $usersetting->courses = json_encode((object) ["4" => false]);

        // Call the function to record the user settings and check, if the record is created.
        $result = $this->testdata->external->execute(
            $usersetting->timefilterpast,
            $usersetting->timefilterfuture,
            $usersetting->basicletter,
            $usersetting->completionletter,
            $usersetting->postletter,
            $usersetting->courses
        );

        $this->assertEquals(true, $result);
        // The existing record is updated, no second record is created.
        $this->assertEquals(1, $DB->count_records('block_townsquare_preferences', ['userid' => $usersetting->userid]));
        $record = $DB->get_record('block_townsquare_preferences', ['userid' => $usersetting->userid]);

        // Check if the record is correct.
        $this->assertEquals($usersetting->timefilterpast, $record->timefilterpast);
        $this->assertEquals($usersetting->timefilterfuture, $record->timefilterfuture);
        $this->assertEquals($usersetting->basicletter, $record->basicletter);
        $this->assertEquals($usersetting->completionletter, $record->completionletter);
        $this->assertEquals($usersetting->postletter, $record->postletter);
        $this->assertEquals($usersetting->courses, $record->courses);
    }

    /**
     * Test the reset_usersettings_method
     * @runInSeparateProcess
     */
    public function test_reset_usersettings(): void {
        global $DB;
        $this->testdata->external = new external\reset_usersettings();
        $user = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Load a usersetting for both users in the database.
        foreach ([$user->id, $user2->id] as $userid) {
            $DB->insert_record('block_townsquare_preferences', ['userid' => $userid, 'timefilterpast' => 432000,
                'timefilterfuture' => 2592000, 'basicletter' => 0, 'completionletter' => 1, 'postletter' => 1, ]);
        }
        $this->assertEquals(1, count($DB->get_records('block_townsquare_preferences', ['userid' => $user->id])));

        // Test case 1: Only the settings of the current user are deleted.
        $this->testdata->external->execute();
        $this->assertEquals(0, count($DB->get_records('block_townsquare_preferences', ['userid' => $user->id])));
        $this->assertEquals(1, count($DB->get_records('block_townsquare_preferences', ['userid' => $user2->id])));

        // Test case 2: normal case.
        $DB->insert_record('block_townsquare_preferences', ['userid' => $user->id, 'timefilterpast' => 432000,
            'timefilterfuture' => 2592000, 'basicletter' => 0, 'completionletter' => 1, 'postletter' => 1, ]);
        $this->assertEquals(1, count($DB->get_records('block_townsquare_preferences', ['userid' => $user->id])));
        $this->testdata->external->execute();
        $this->assertEquals(0, count($DB->get_records('block_townsquare_preferences', ['userid' => $user->id])));

        // Test case 3: Resetting without existing settings does not fail.
        $this->assertTrue($this->testdata->external->execute());
        $this->assertEquals(1, count($DB->get_records('block_townsquare_preferences', ['userid' => $user2->id])));
    }
}
