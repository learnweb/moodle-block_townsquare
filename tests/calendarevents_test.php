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


namespace block_townsquare;

use testing_data_generator;
/**
 * Unit tests for the block_townsquare.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * PHPUnit tests for testing the process of collecting post events.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \townsquareevents::townsquare_get_calendarevents()
 */
class calendarevents_test extends \advanced_testcase {

    // Attributes.

    /** @var stdClass The data that will be used for testing.
     * This Class contains the test data:
     * - course1, course2
     * - forum1, moodleoverflow1 (With one post each)
     * - forum2, moodleoverflow2 (With one post each)
     * - teacher
     * - student1, student2
     */
    private $testdata;

    /** @var townsquareevents Class to get townsquare events. */
    private $townsquareevents;

    // Construct functions.
    public function setUp() : void {
        $this->resetAfterTest();
        $this->helper_course_set_up();
        $this->townsquareevents = new townsquareevents();
    }

    public function tearDown() : void {
        $this->townsquareevents = null;
        $this->testdata = null;
    }

    // Tests.

    /**
     * Test, if calendar events are collected correctly.
     */
    public function test_calendarevents() : void {

    }

    // Helper functions.

    /**
     * Helper function that creates:
     * - two courses with a forum and a moodleoverflow
     * - a teacher, who creates a post in each forum and moodleoverflow.
     * - a student in each course
     */
    private function helper_course_set_up() : void {

    }
}
