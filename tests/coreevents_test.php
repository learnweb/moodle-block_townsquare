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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/completionlib.php');

use core_completion_external;
use stdClass;

/**
 * Unit tests for the block_townsquare.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * PHPUnit tests for testing the process of collecting calendar events from core plugins.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_townsquare\townsquareevents::get_deadlines()
 * @covers \block_townsquare\output\content
 * @covers \block_townsquare\observer::course_deleted()
 */
final class coreevents_test extends \advanced_testcase {
    // Attributes.

    /** @var object The data that will be used for testing.
     * This Class contains the test data:
     * - two courses.
     * - an assignment in each course.
     * - an activity completion in the first course.
     * - a teacher that is enrolled in both courses.
     * - a student in each course.
     */
    private $testdata;

    // Construct functions.
    public function setUp(): void {
        global $CFG;
        parent::setUp();
        $CFG->enablecompletion = true;
        $this->testdata = new stdClass();
        $this->resetAfterTest();
        $this->helper_course_set_up();
    }

    public function tearDown(): void {
        $this->testdata = null;
        parent::tearDown();
    }

    // Tests.

    /**
     * Test, if calendar events are sorted correctly.
     */
    public function test_sortorder(): void {
        // Get the current calendar events from the teacher.
        $coreevents = $this->get_coreevents_from_user($this->testdata->teacher);
        $count = count($coreevents);

        // Iterate through all posts and check if the sort order is correct.
        $result = true;
        for ($i = 0; $i < $count - 1; $i++) {
            if ($coreevents[$i]->time < $coreevents[$i + 1]->time) {
                $result = false;
                break;
            }
        }

        $this->assertEquals(true, $result);
    }

    /**
     * Test, if the post events are processed correctly if the course disappears and
     * if the course deleted observer cleans up the course filter preferences.
     * @return void
     */
    public function test_course_deleted(): void {
        global $DB;
        // Delete the course from the database.
        $DB->delete_records('course', ['id' => $this->testdata->course1->id]);

        // Get the post events from the teacher.
        $coreevents = $this->get_coreevents_from_user($this->testdata->teacher);

        // There should be no posts from the first course.
        $this->assertEquals(true, empty(array_filter($coreevents, fn($event) => $event->courseid == $this->testdata->course1->id)));

        // Create preferences: the teacher has a course filter setting, student2 has an old record without one.
        $course2id = $this->testdata->course2->id;
        $similarid = $course2id . '1';
        $preference = [
            'timefilterpast' => 432000,
            'timefilterfuture' => 432000,
        ];
        $teacherpref = $DB->insert_record('block_townsquare_preferences', $preference + [
            'userid' => $this->testdata->teacher->id,
            'courses' => json_encode((object) [$this->testdata->course1->id => true, $course2id => false, $similarid => true]),
        ]);
        $studentpref = $DB->insert_record('block_townsquare_preferences', $preference + [
            'userid' => $this->testdata->student2->id,
            'courses' => null,
        ]);

        // Delete the second course properly, so that the course deleted event is triggered.
        delete_course($this->testdata->course2, false);

        // Only the deleted course should be removed from the teacher's course filter, other settings stay.
        $teachersetting = $DB->get_record('block_townsquare_preferences', ['id' => $teacherpref]);
        $expected = json_encode((object) [$this->testdata->course1->id => true, $similarid => true]);
        $this->assertEquals($expected, $teachersetting->courses);
        $this->assertEquals(432000, $teachersetting->timefilterpast);

        // The record without a course filter setting should stay untouched.
        $this->assertNull($DB->get_field('block_townsquare_preferences', 'courses', ['id' => $studentpref]));
    }

    /**
     * Test, if the users see only posts of their courses.
     * @return void
     */
    public function test_coursefilter(): void {
        // Test case 1: Posts for the teacher.
        $coreevents = $this->get_coreevents_from_user($this->testdata->teacher);

        // Two checks: Is every event in the course of the teacher and is the number of events correct.
        $result = $this->check_eventcourses($coreevents, enrol_get_all_users_courses($this->testdata->teacher->id, true));
        $this->assertEquals(true, $result);
        $this->assertEquals(5, count($coreevents));

        // Test case 2: Post for a student.
        $coreevents = $this->get_coreevents_from_user($this->testdata->student2);

        // Two checks: Is every event in the course of the student and is the number of events correct.
        $result = $this->check_eventcourses($coreevents, enrol_get_all_users_courses($this->testdata->student2->id, true));
        $this->assertEquals(true, $result);
        $this->assertEquals(1, count($coreevents));
    }

    /**
     * Test, if an assignment is displayed correctly
     * @return void
     */
    public function test_assignfilter(): void {
        // Test case 1: An Assignment is over a week due.
        $time = time();
        $pastassignment = $this->create_assignment(
            $this->testdata->course1->id,
            $time - 1814400,
            $time - 907200,
            $time - 907200,
            false
        );

        // Get the current calendar events.
        $coreevents = $this->get_coreevents_from_user($this->testdata->student1);

        // The assignment should not appear.
        $this->assertEquals(true, empty(array_filter($coreevents, fn($event) => $event->cmid == $pastassignment->cmid)));

        // Test case 2: The student should not see the gradingdue event, the teacher should see it.
        // First the events of the student.
        $gradingdue = get_string('calendargradingdue', 'assign', $this->testdata->assignment1->name);
        $this->assertEquals(true, empty(array_filter($coreevents, fn($event) => $event->name == $gradingdue)));

        // Then the events of the teacher.
        $coreevents = $this->get_coreevents_from_user($this->testdata->teacher);
        $this->assertEquals(true, !empty(array_filter($coreevents, fn($event) => $event->name == $gradingdue)));

        // Test case 3: Assignments that are not open should not be seen.
        $notopenassignment = $this->create_assignment(
            $this->testdata->course1->id,
            $time + 3600,
            $time + 604800,
            $time + 604800,
            false
        );
        $coreevents = $this->get_coreevents_from_user($this->testdata->student1);
        $this->assertEquals(true, empty(array_filter($coreevents, fn($e) => $e->cmid == $notopenassignment->cmid)));
    }

    /**
     * Test if the deadlines of the same course and module at the same time are grouped in one letter.
     * @return void
     */
    public function test_assigngroups(): void {
        // Create 2 assignments that are due at the same time.
        $time = time();
        $cid = $this->testdata->course2->id;
        $assignone = $this->create_assignment($cid, $time - 1814400, $time + 907200, $time + 1814400, false);
        $assigntwo = $this->create_assignment($cid, $time - 907200, $time + 907200, $time + 1814400, false);

        // There should be 2 letters for the student: the grouped due dates and the default assignment of the course.
        $letters = $this->get_letters_from_user($this->testdata->student2);
        $grouped = array_values(array_filter($letters, fn($letter) => $letter['grouped']));
        $this->assertCount(2, $letters);
        $this->assertCount(1, $grouped);

        // The grouped deadlines are sorted by their name.
        $this->assertEquals([
            get_string('calendardue', 'assign', $assignone->name),
            get_string('calendardue', 'assign', $assigntwo->name),
        ], array_column($grouped[0]['deadlines'], 'name'));

        // The teacher also sees the grading due dates. The due dates of the two assignments, their grading due dates and
        // the due and grading due date of the default assignment (both at the same time) are grouped. The letters closest
        // to now come first.
        $letters = $this->get_letters_from_user($this->testdata->teacher);
        $letters = array_values(array_filter($letters, fn($letter) => $letter['courseid'] == $cid));
        $grouped = array_values(array_filter($letters, fn($letter) => $letter['grouped']));
        $this->assertCount(3, $letters);
        $this->assertCount(3, $grouped);
        $assignment2 = $this->testdata->assignment2->name;
        $this->assertEquals([
            get_string('calendardue', 'assign', $assignment2),
            get_string('calendargradingdue', 'assign', $assignment2),
        ], array_column($grouped[1]['deadlines'], 'name'));
        $this->assertEquals([
            get_string('calendargradingdue', 'assign', $assignone->name),
            get_string('calendargradingdue', 'assign', $assigntwo->name),
        ], array_column($grouped[2]['deadlines'], 'name'));
    }

    /**
     * Test, if a activity completion is displayed correctly.
     * @return void
     */
    public function test_activitycompletion(): void {
        // Test case 1: The student should see the activity completion event.
        $coreevents = $this->get_coreevents_from_user($this->testdata->student1);
        $result = false;
        $count = 0;
        $completion = get_string('completionexpectedfor', 'completion',
            (object) ['instancename' => $this->testdata->assignment1->name]);
        foreach ($coreevents as $event) {
            if ($event->name == $completion) {
                $result = true;
                $count++;
            }
        }
        $this->assertEquals(true, $result);
        $this->assertEquals(1, $count);

        // Test case 2: The student marks the assignment as completed, the activity completion event should disappear.
        core_completion_external::update_activity_completion_status_manually($this->testdata->assignment1->cmid, true);

        $coreevents = $this->get_coreevents_from_user($this->testdata->student1);
        $this->assertEquals(true, empty(array_filter($coreevents, fn($event) => $event->name == $completion)));
    }

    // Helper functions.

    /**
     * Helper function that creates:
     * - two courses.
     * - an assignment in each course.
     * - an activity completion in the first course.
     * - a teacher that is enrolled in both courses.
     * - a student in each course.
     */
    private function helper_course_set_up(): void {
        $datagenerator = $this->getDataGenerator();

        // Create a new course.
        $this->testdata->course1 = $datagenerator->create_course(['enablecompletion' => 1]);
        $this->testdata->course2 = $datagenerator->create_course(['enablecompletion' => 1]);

        // Create a teacher and a student and enroll them in the course.
        $this->testdata->teacher = $datagenerator->create_user();
        $this->testdata->student1 = $datagenerator->create_user();
        $this->testdata->student2 = $datagenerator->create_user();

        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course1->id, 'teacher');
        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course2->id, 'teacher');
        $datagenerator->enrol_user($this->testdata->student1->id, $this->testdata->course1->id, 'student');
        $datagenerator->enrol_user($this->testdata->student2->id, $this->testdata->course2->id, 'student');

        // Create an assign module with an activity completion.
        // Make an assignment that is due in 1 week and will be graded in 2 weeks.
        $time = time();
        $this->testdata->assignment1 = $this->create_assignment(
            $this->testdata->course1->id,
            $time - 3600,
            $time + 1209600,
            $time + 1209600,
            true
        );

        // Create a second assignment for the second course.
        $this->testdata->assignment2 = $this->create_assignment(
            $this->testdata->course2->id,
            $time - 3600,
            $time + 1209600,
            $time + 1209600,
            false
        );
    }

    /**
     * Helper function to create an assignment.
     * @param int $courseid              id of the course
     * @param int $allowsubmissions      timestamp
     * @param int $due                   timestamp
     * @param int $gradingdue            timestamp
     * @param bool $completion if an activity completion should be generated
     * @return object
     */
    private function create_assignment(int $courseid, int $allowsubmissions, int $due, int $gradingdue, bool $completion): object {
        // Create an activity completion for the assignment if wanted.
        $options = $completion ? ['completion' => COMPLETION_TRACKING_MANUAL, 'completionexpected' => $due] : [];

        $assignrecord = (object) [
            'course' => $courseid,
            'courseid' => $courseid,
            'duedate' => $due,
            'allowsubmissionsfromdate' => $allowsubmissions,
            'gradingduedate' => $gradingdue,
        ];
        return $this->getDataGenerator()->create_module('assign', $assignrecord, $options);
    }

    /**
     * Helper function to get the events from a certain user.
     * @param object $user  The user for whom the events should be collected (townsquareevents.php uses $USER).
     * @return array
     */
    private function get_coreevents_from_user($user): array {
        $this->setUser($user);
        $townsquareevents = new townsquareevents();
        return $townsquareevents->get_deadlines();
    }

    /**
     * Helper function to get the exported letters of a certain user.
     * @param object $user The user for whom the letters should be built.
     * @return array
     */
    private function get_letters_from_user($user): array {
        global $PAGE;
        $this->setUser($user);
        $content = new output\content();
        $lettergroups = $content->group_by_day($content->get_letters());
        $letters = array_merge(...array_map(fn($lettergroup) => $lettergroup->get_letters(), $lettergroups));
        return array_map(fn($letter) => $letter->export_for_template($PAGE->get_renderer('core')), $letters);
    }


    /**
     * Helper function to check if all events are in the courses of the user.
     * @param array $events
     * @param array $enrolledcourses
     * @return bool
     */
    private function check_eventcourses(array $events, array $enrolledcourses): bool {
        $enrolledcoursesids = array_map(fn($course) => $course->id, $enrolledcourses);
        return (empty(array_filter($events, fn($event) => !in_array($event->courseid, $enrolledcoursesids))));
    }
}
