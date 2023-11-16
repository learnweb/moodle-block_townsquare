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

use stdClass;
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
     * - tow courses
     * - a teacher,
     * - a student of the course
     * - one assign module (with a date when the assignment is due)
     * - an activity completion from the assign module
     * -
     */
    private $testdata;

    // Construct functions.
    public function setUp() : void {
        $this->testdata = new stdClass();
        $this->resetAfterTest();
        $this->helper_course_set_up();

    }

    public function tearDown() : void {
        $this->testdata = null;
    }

    // Tests.

    /**
     * Test, if calendar events are sorted correctly.
     */
    public function test_sortorder() : void {
        // Set the teacher as the current logged in user.
        $this->setUser($this->testdata->teacher);

        // Get the current post events.
        $townsquareevents = new townsquareevents();
        $calendarevents = $townsquareevents->townsquare_get_calendarevents();
        var_dump($calendarevents);
        // Iterate trough all posts and check if the sort order is correct.
        $timestamp = 9999999999;
        $result = true;
        foreach ($calendarevents as $event) {
            if ($timestamp >= $event->timestart) {
                $timestamp = $event->timestart;
            } else {
                $result = false;
            }
        }

        $this->assertEquals(true, $result);
    }

    /**
     * Test, if the users see only posts of their courses.
     * @return void
     */
    public function test_course() : void {

    }

    /**
     * Test, if a assignment is displayed correctly
     * @return void
     */
    public function test_assignfilter() : void {

    }

    /**
     * Test, if a activity completion is displayed correctly.
     * @return void
     */
    public function test_activitycompletion() : void {

    }

    // Helper functions.

    /**
     * Helper function that creates:
     * - two courses with a forum and a moodleoverflow
     * - a teacher, who creates a post in each forum and moodleoverflow.
     * - a student in each course
     */
    private function helper_course_set_up() : void {
        global $DB;
        $datagenerator = $this->getDataGenerator();

        // Create a new course.
        $this->testdata->course = $datagenerator->create_course(['enablecompletion' => 1]);

        // Create a teacher and a student and enroll them in the course.
        $this->testdata->teacher = $datagenerator->create_user();
        $this->testdata->student = $datagenerator->create_user();
        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course->id, 'teacher');
        $datagenerator->enrol_user($this->testdata->student->id, $this->testdata->course->id, 'student');

        // Create an assign module and a activity completion.
        // Make an assignment that is due in 1 week and will be graded in 2 weeks.
        $time = time();
        $this->testdata->assignment = $this->create_assignment($time, $time + 1209600, $time + 1209600);
    }


    private function create_assignment($allowsubmittsionsfromdate, $duedate, $gradingduedate) {
        $assignrecord = [
            'course'                            => $this->testdata->course->id,
            'duedate'                           => $duedate,
            'allowsubmissionsfromdate'          => $allowsubmittsionsfromdate,
            'gradingduedate'                    => $gradingduedate,
        ];
        return $this->getDataGenerator()->create_module('assign', $assignrecord, ['completion' => 1]);
    }
}
