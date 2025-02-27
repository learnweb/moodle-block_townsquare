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

/**
 * Unit tests for the block_townsquare.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * PHPUnit tests for testing the process of create the townsquare letters.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_townsquare\contentcontroller
 */
final class contentcontroller_test extends \advanced_testcase {

    // Attributes.

    /** @var object The data that will be used for testing
     * This Class contains:
     * - A Course
     * - A User
     * - One forum and moodleoverflow with one post each
     * - One assignment with an activity completion
     */
    private $testdata;

    /** @var bool If the moodleoverflow module is available.
     * This Plugin can support moodleoverflow, but it is not necessary to have it installed.
     */
    private bool $modoverflowinstalled;

    // Construct functions.

    public function setUp(): void {
        $this->testdata = new stdClass();
        $this->resetAfterTest();
        $this->helper_course_set_up();
    }

    public function tearDown(): void {
        $this->testdata = null;
    }

    // Tests.

    /**
     * Test, if the right letters are created.
     * @return void
     */
    public function test_letters(): void {
        // Set an logged in user.
        $this->setUser($this->testdata->teacher);

        // Create the controller and get the townsquareevents.
        $controller = new contentcontroller();
        $content = $controller->build_content();
        $events = $controller->events;

        // Check the lettertype for each letter.
        $result = true;
        $length = count($content); // The content is one object larger than the townsquareevents (because of the orientationmarker).

        for ($i = 0; $i < $length; $i++) {
            // The content has an orientation marker.
            if (isset(current($content)['isorientationmarker'])) {
                next($content);
                continue;
            }

            // Declare the three types of letter checks.
            $postcheck = $this->check_two_params(current($events)->eventtype, 'post', current($content)['lettertype'], 'post');
            $completioncheck = $this->check_two_params(current($events)->eventtype, 'expectcompletionon',
                                                       current($content)['lettertype'], 'activitycompletion');

            $basiccheck = (current($events)->eventtype != 'post' && current($events)->eventtype != 'expectcompletionon') &&
                           current($content)['lettertype'] == 'basic';

            // If one of the checks fails there is a problem while creating the letters.
            if (!$postcheck && !$completioncheck && !$basiccheck) {
                $result = false;
                break;
            }

            next($content);
            next($events);
        }

        if ($this->modoverflowinstalled) {
            $this->assertEquals(6, count($content));
        } else {
            $this->assertEquals(5, count($content));
        }
        $this->assertEquals(true, $result);
    }

    // Helper functions.

    /**
     * Helper function that sets up the testdata.
     * @return void
     */
    private function helper_course_set_up(): void {
        global $DB;
        // Declare generators.
        $datagenerator = $this->getDataGenerator();
        $forumgenerator = $datagenerator->get_plugin_generator('mod_forum');

        // Create a course and a user.
        $this->testdata->course = $datagenerator->create_course(['enablecompletion' => 1]);
        $this->testdata->teacher = $datagenerator->create_user();
        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course->id, 'teacher');

        // Create a forum and a moodleoverflow with posts.
        $this->testdata->forum = $datagenerator->create_module('forum', ['course' => $this->testdata->course->id]);
        $record = (array)$this->testdata->forum + ['forum' => $this->testdata->forum->id, 'userid' => $this->testdata->teacher->id];
        $this->testdata->fdiscussion = (object)$forumgenerator->create_discussion($record);

        if ($DB->get_record('modules', ['name' => 'moodleoverflow', 'visible' => 1])) {
            $this->modoverflowinstalled = true;
            $modoverflowgenerator = $datagenerator->get_plugin_generator('mod_moodleoverflow');
            $this->testdata->moodleoverflow = $datagenerator->create_module('moodleoverflow',
                                                                             ['course' => $this->testdata->course->id]);
            $this->testdata->mdiscussion = $modoverflowgenerator->post_to_forum($this->testdata->moodleoverflow,
                                                                                   $this->testdata->teacher);
        } else {
            $this->modoverflowinstalled = false;
        }

        // Create an assign module with activity completion.
        $time = time();
        $this->testdata->assignment = $this->create_assignment($time - 3600, $time + 86400, $time + 172800);
    }

    /**
     * Helper function to create an assignment.
     * @param int $allowsubmissionsdate      timestamp
     * @param int $duedate                   timestamp
     * @param int $gradingduedate            timestamp
     * @return object
     */
    private function create_assignment($allowsubmissionsdate, $duedate, $gradingduedate): object {
        // Create an activity completion for the assignment if wanted.
        $options = [
            'completion' => COMPLETION_TRACKING_MANUAL,
            'completionexpected' => $duedate,
        ];

        $assignrecord = [
            'course' => $this->testdata->course->id,
            'courseid' => $this->testdata->course->id,
            'duedate' => $duedate,
            'allowsubmissionsfromdate' => $allowsubmissionsdate,
            'gradingduedate' => $gradingduedate,
        ];
        return $this->getDataGenerator()->create_module('assign', $assignrecord, $options);
    }

    /**
     * Little helper function to reduce cyclomatic complexity. Checks if two params equal values.
     * @param mixed $param1
     * @param mixed $equal1
     * @param mixed $param2
     * @param mixed $equal2
     * @return bool
     */
    private function check_two_params($param1, $equal1, $param2, $equal2) {
        return $param1 == $equal1 && $param2 == $equal2;
    }
}
