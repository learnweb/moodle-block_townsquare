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
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_townsquare;

use stdClass;
use testing_data_generator;

/**
 * PHPUnit tests for testing the process of collecting events.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \townsquareevents
 */
class townsquareevents_test extends \advanced_testcase {

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
     * Test, if post events are collected correctly.
     * @return void
     */
    public function test_postevents() : void {

    }

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
        global $DB;
        $datagenerator = $this->getDataGenerator();

        // Create two new courses.
        $this->testdata->course1 = $datagenerator->create_course();
        $course1location = ['course' => $this->testdata->course1->id];
        $this->testdata->course2 = $datagenerator->create_course();
        $course2location = ['course' => $this->testdata->course2->id];

        // Create a teacher and enroll the teacher in both courses.
        $this->testdata->teacher = $datagenerator->create_user(['firstname' => 'Tamaro', 'lastname' => 'Walter']);
        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course1->id, 'teacher');
        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course2->id, 'teacher');

        // Create two students.
        $this->testdata->student1 = $this->getDataGenerator()->create_user(['firstname' => 'Ava', 'lastname' => 'Davis']);
        $this->getDataGenerator()->enrol_user($this->testdata->user1->id, $this->testdata->course1->id, 'student');
        $this->testdata->student2 = $this->getDataGenerator()->create_user(['firstname' => 'Ethan', 'lastname' => 'Brown']);
        $this->getDataGenerator()->enrol_user($this->testdata->user2->id, $this->testdata->course2->id, 'student');

        // Create a moodleoverflow with a post in each course.
        $moodleoverflowgenerator = $datagenerator->get_plugin_generator('mod_moodleoverflow');

        $this->testdata->moodleoverflow1 = $datagenerator->create_module('moodleoverflow', $course1location);
        $this->testdata->mdiscussion1 = $moodleoverflowgenerator->post_to_forum($this->testdata->moodleoverflow1,
                                                                                $this->testdata->teacher);
        $this->testdata->mpost1 = $DB->get_record('moodleoverflow_posts', ['id' => $this->testdata->mdiscussion1[0]->firstpost]);

        $this->testdata->moodleoverflow2 = $datagenerator->create_module('moodleoverflow', $course2location);
        $this->testdata->mdiscussion2 = $moodleoverflowgenerator->post_to_forum($this->testdata->moodleoverflow2,
                                                                                $this->testdata->teacher);
        $this->testdata->mpost2 = $DB->get_record('moodleoverflow_posts', ['id' => $this->testdata->mdiscussion2[0]->firstpost]);

        // Create a forum with a post in each course.
        $forumgenerator = $datagenerator->get_plugin_generator('mod_forum');

        $this->testdata->forum1 = $datagenerator->create_module('forum', $course1location);
        $this->testdata->fdiscussion1 = $forumgenerator->post_to_forum($this->testdata->forum1, $this->testdata->teacher);
        $this->testdata->fpost1 = $DB->get_record('forum_posts', ['id' => $this->testdata->fdiscussion1[0]->firstpost]);

        $this->testdata->forum2 = $datagenerator->create_module('forum', $course2location);
        $this->testdata->fdiscussion2 = $forumgenerator->post_to_forum($this->testdata->forum2, $this->testdata->teacher);
        $this->testdata->fpost2 = $DB->get_record('forum_posts', ['id' => $this->testdata->fdiscussion2[0]->firstpost]);
    }
}
