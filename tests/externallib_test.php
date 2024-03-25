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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use block_townsquare\external\block_townsquare_external;
use core_external\external_api;
/**
 * PHPUnit tests for testing the process of the externallib.
 *
 * @package   block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_townsquare\block_townsquare_external::record_usersettings
 */
class externallib_test extends \advanced_testcase {

    public function setUp(): void {
        global $CFG;
        require_once($CFG->dirroot . '/blocks/townsquare/externallib.php');
        $this->resetAfterTest();
    }

    /**
     * Tests the record_usersettings method.
     * @runInSeparateProcess
     */
    public function test_record_usersettings(): void {
        global $DB;

        $usersetting = new \stdClass();
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
        $result = block_townsquare_external::record_usersettings($usersetting->userid, $usersetting->timefilterpast,
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
        $result = block_townsquare_external::record_usersettings($usersetting->userid, $usersetting->timefilterpast,
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
}
