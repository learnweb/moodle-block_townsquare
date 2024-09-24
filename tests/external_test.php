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
 * @covers \external
 * @runTestsInSeparateProcesses
 */
final class external_test extends \advanced_testcase {

    /** @var object That that is used for testing
     * It contains an instance of the townsquare external class.
     * */
    private $testdata;

    public function setUp(): void {
        $this->resetAfterTest();
        $this->testdata = new stdClass();
        $this->testdata->external = new external();
    }

    /**
     * Tests the record_usersettings method.
     * @runInSeparateProcess
     */
    public function test_record_usersettings(): void {
        global $DB;

        $usersetting = new stdClass();
        $usersetting->userid = 1;
        $usersetting->timefilterpast = 432000;
        $usersetting->timefilterfuture = 2592000;
        $usersetting->basicletter = 0;
        $usersetting->completionletter = 1;
        $usersetting->postletter = 1;

        // Test Case 1: The User sets the setting for the first time.

        // Check that there is no record in the database.
        $record = $DB->get_record('block_townsquare_preferences', ['userid' => $usersetting->userid]);
        $this->assertEquals(false, $record);

        // Call the function to record the user settings and check, if the record is created.
        $result = $this->testdata->external->record_usersettings($usersetting->userid,
                                                                  $usersetting->timefilterpast,
                                                                  $usersetting->timefilterfuture, $usersetting->basicletter,
                                                                  $usersetting->completionletter, $usersetting->postletter);

        $this->assertEquals(true, $result);
        $record = $DB->get_record('block_townsquare_preferences', ['userid' => $usersetting->userid]);

        // Check if the record is correct.
        $this->assertEquals($usersetting->timefilterpast, $record->timefilterpast);
        $this->assertEquals($usersetting->timefilterfuture, $record->timefilterfuture);
        $this->assertEquals($usersetting->basicletter, $record->basicletter);
        $this->assertEquals($usersetting->completionletter, $record->completionletter);
        $this->assertEquals($usersetting->postletter, $record->postletter);

        // Test Case 2: The User updates the settings.
        $usersetting->timefilterpast = 2592000;
        $usersetting->timefilterfuture = 0;
        $usersetting->basicletter = 1;
        $usersetting->completionletter = 0;
        $usersetting->postletter = 0;

        // Call the function to record the user settings and check, if the record is created.
        $result = $this->testdata->external->record_usersettings($usersetting->userid, $usersetting->timefilterpast,
                                                                  $usersetting->timefilterfuture, $usersetting->basicletter,
                                                                  $usersetting->completionletter, $usersetting->postletter);

        $this->assertEquals(true, $result);
        $record = $DB->get_record('block_townsquare_preferences', ['userid' => $usersetting->userid]);

        // Check if the record is correct.
        $this->assertEquals($usersetting->timefilterpast, $record->timefilterpast);
        $this->assertEquals($usersetting->timefilterfuture, $record->timefilterfuture);
        $this->assertEquals($usersetting->basicletter, $record->basicletter);
        $this->assertEquals($usersetting->completionletter, $record->completionletter);
        $this->assertEquals($usersetting->postletter, $record->postletter);
    }

    /**
     * Test the reset_usersettings_method
     */
    public function test_reset_usersettings(): void {
        global $DB;
        // Load a usersetting in the database.
        $DB->insert_record('block_townsquare_preferences', ['userid' => 1, 'timefilterpast' => 432000,
                            'timefilterfuture' => 2592000, 'basicletter' => 0, 'completionletter' => 1, 'postletter' => 1, ]);
        $this->assertEquals(1, count($DB->get_records('block_townsquare_preferences', ['userid' => 1])));

        // Test case 1: Wrong parameters.
        $this->testdata->external->reset_usersettings(2);
        $this->assertEquals(1, count($DB->get_records('block_townsquare_preferences', ['userid' => 1])));

        // Test case 2: For some reason, many records from the same user exist.
        $DB->insert_record('block_townsquare_preferences', ['userid' => 1, 'timefilterpast' => 432000,
            'timefilterfuture' => 2592000, 'basicletter' => 1, 'completionletter' => 0, 'postletter' => 0, ]);
        $this->assertEquals(2, count($DB->get_records('block_townsquare_preferences', ['userid' => 1])));

        $this->testdata->external->reset_usersettings(1);
        $this->assertEquals(0, count($DB->get_records('block_townsquare_preferences', ['userid' => 1])));

        // Test case 3: normal case.
        $DB->insert_record('block_townsquare_preferences', ['userid' => 1, 'timefilterpast' => 432000,
            'timefilterfuture' => 2592000, 'basicletter' => 0, 'completionletter' => 1, 'postletter' => 1, ]);
        $this->assertEquals(1, count($DB->get_records('block_townsquare_preferences', ['userid' => 1])));
        $this->testdata->external->reset_usersettings(1);
        $this->assertEquals(0, count($DB->get_records('block_townsquare_preferences', ['userid' => 1])));
    }
}
