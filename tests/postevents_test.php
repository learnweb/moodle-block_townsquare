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

/**
 * PHPUnit tests for testing the process of collecting post events.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_townsquare\townsquareevents::townsquare_get_postevents()
 */
class postevents_test extends \advanced_testcase {

    // Attributes.

    /** @var object The data that will be used for testing.
     * This Class contains the test data:
     * - course1, course2
     * - forum1, forum2 (with one post each)
     * - moodleoverflow1, moodleoverflow2 (With two post each)
     * - teacher (write one post in each forum and moodleoverflow)
     * - student1, student2 (write one post in their moodleoverflow)
     */
    private $testdata;

    /** @var bool If the moodleoverflow module is available.
     * This Plugin can support moodleoverflow, but it is not necessary to have it installed.
     */
    private $moodleoverflowavailable;

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
     * Test, if post events are sorted correctly.
     * Post should be sorted by the time they were created in descending order (newest post first).
     * @return void
     */
    public function test_sortorder(): void {
        // Get the current post events from the teacher.
        $posts = $this->get_postevents_from_user($this->testdata->teacher);

        // Iterate trough all posts and check if the sort order is correct.
        $timestamp = 9999999999;
        $result = true;
        foreach ($posts as $post) {
            if ($timestamp >= $post->postcreated) {
                $timestamp = $post->postcreated;
            } else {
                $result = false;
            }
        }

        $this->assertEquals(true, $result);
    }

    /**
     * Test, if the post events are processed correctly if the moodleoverflow module is not installed.
     * @return void
     */
    public function test_module_moodleoverflow(): void {
        global $DB;
        if (!$this->moodleoverflowavailable) {
            return;
        }
        // Test case: disable moodleoverflow.
        $DB->delete_records('modules', ['name' => 'moodleoverflow']);

        // Get the post events from the teacher.
        $posts = $this->get_postevents_from_user($this->testdata->teacher);

        $result = true;
        // Check if there are no more moodleoverflow posts.
        foreach ($posts as $post) {
            if ($post->modulename == 'moodleoverflow') {
                $result = false;
            }
        }

        // Two Checks: The number of posts (there are only 2 forum posts).
        $this->assertEquals(2, count($posts));
        $this->assertEquals(true, $result);
    }

    /**
     * Test, if the post events are processed correctly if the forum module is not installed.
     * @return void
     */
    public function test_module_forum(): void {
        global $DB;
        // Test case: disable forum.
        $DB->delete_records('modules', ['name' => 'forum']);

        // Get the post events from the teacher.
        $posts = $this->get_postevents_from_user($this->testdata->teacher);

        $result = true;
        // Check if there are no more moodleoverflow posts.
        foreach ($posts as $post) {
            if ($post->modulename == 'forum') {
                $result = false;
            }
        }

        // Two Checks: The number of posts (there are 4 moodleoverflow posts) and the result.
        if ($this->moodleoverflowavailable) {
            $this->assertEquals(4, count($posts));
        } else {
            $this->assertEquals(0, count($posts));
        }
        $this->assertEquals(true, $result);
    }

    /**
     * Test, if the post events are processed correctly if the course disappears.
     * @return void
     */
    public function test_course_deleted() {
        global $DB;
        // Delete the course from the database.
        $DB->delete_records('course', ['id' => $this->testdata->course1->id]);

        // Get the post events from the teacher.
        $posts = $this->get_postevents_from_user($this->testdata->teacher);

        // There should be no posts from the first course.
        $result = true;
        foreach ($posts as $post) {
            if ($post->courseid == $this->testdata->course1->id) {
                $result = false;
            }
        }
        $this->assertEquals(true, $result);
    }

    /**
     * Test, if the users see only posts of their courses.
     * @return void
     */
    public function test_coursefilter(): void {
        // Test case 1: Post for the teacher.
        $posts = $this->get_postevents_from_user($this->testdata->teacher);;

        // Check if the teacher sees only posts of his courses.
        $result = $this->check_postcourses($posts, enrol_get_all_users_courses($this->testdata->teacher->id));

        // Two Checks: Is the number of posts correct (no post is missing) and is every post in the course of the teacher.
        if ($this->moodleoverflowavailable) {
            $this->assertEquals(6, count($posts));
        } else {
            $this->assertEquals(2, count($posts));
        }
        $this->assertEquals(true, $result);

        // Test case 2: Post for the first student.
        $posts = $this->get_postevents_from_user($this->testdata->student1);

        $result = $this->check_postcourses($posts, enrol_get_all_users_courses($this->testdata->student1->id));

        if ($this->moodleoverflowavailable) {
            $this->assertEquals(3, count($posts));
        } else {
            $this->assertEquals(1, count($posts));
        }
        $this->assertEquals(true, $result);

        // Test case 3: Post for the second student.
        $posts = $this->get_postevents_from_user($this->testdata->student2);

        $result = $this->check_postcourses($posts, enrol_get_all_users_courses($this->testdata->student2->id));

        if ($this->moodleoverflowavailable) {
            $this->assertEquals(3, count($posts));
        } else {
            $this->assertEquals(1, count($posts));
        }
        $this->assertEquals(true, $result);
    }

    /**
     * Test, if data in moodleoverflow posts is processed correctly when the moodleoverflow is anonymous.
     * @return void
     */
    public function test_anonymous(): void {
        if (!$this->moodleoverflowavailable) {
            return;
        }

        // Set the first moodleoverflow to partially anonymous and the second to fully anonymous.
        $this->make_anonymous($this->testdata->moodleoverflow1, 1);
        $this->make_anonymous($this->testdata->moodleoverflow2, 2);

        // Get the current post events from the teacher..
        $posts = $this->get_postevents_from_user($this->testdata->teacher);

        // Posts of the first moodleoverflow.
        $firstteacherpost = null;
        $firststudentpost = null;

        // Posts of the second moodleoverflow.
        $secondteacherpost = null;
        $secondstudentpost = null;

        // Iterate through all posts and save the posts from teacher and student.
        foreach ($posts as $post) {
            if ($post->modulename == 'moodleoverflow' && $post->moodleoverflowid == $this->testdata->moodleoverflow1->id) {
                if ($post->postuserid == $this->testdata->teacher->id) {
                    $firstteacherpost = $post;
                } else if ($post->postuserid == $this->testdata->student1->id) {
                    $firststudentpost = $post;
                }
            } else if ($post->modulename == 'moodleoverflow' && $post->moodleoverflowid == $this->testdata->moodleoverflow2->id) {
                if ($post->postuserid == $this->testdata->teacher->id) {
                    $secondteacherpost = $post;
                } else if ($post->postuserid == $this->testdata->student2->id) {
                    $secondstudentpost = $post;
                }
            }
        }

        // Test case 1: The teacherpost should be anonymous and the studentpost should not be anonymous (partial anonymous).
        $this->assertEquals(true, $firstteacherpost->anonymous);
        $this->assertEquals(false, $firststudentpost->anonymous);

        // Test case 2: The teacherpost and studentpost should be anonymous (fully anonymous).
        $this->assertEquals(true, $secondteacherpost->anonymous);
        $this->assertEquals(true, $secondstudentpost->anonymous);
    }

    // Helper functions.

    /**
     * Helper function that creates:
     * - two courses with a forum and a moodleoverflow
     * - a teacher, who creates a post in each forum and moodleoverflow.
     * - a student in each course
     *
     */
    private function helper_course_set_up(): void {
        global $DB;
        $datagenerator = $this->getDataGenerator();
        // Create two new courses.
        $this->testdata->course1 = $datagenerator->create_course();
        $course1location = ['course' => $this->testdata->course1->id];

        $this->testdata->course2 = $datagenerator->create_course();
        $course2location = ['course' => $this->testdata->course2->id];

        // Create a teacher and enroll the teacher in both courses.
        $this->testdata->teacher = $datagenerator->create_user();
        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course1->id, 'teacher');
        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course2->id, 'teacher');

        // Create two students.
        $this->testdata->student1 = $datagenerator->create_user();
        $this->getDataGenerator()->enrol_user($this->testdata->student1->id, $this->testdata->course1->id, 'student');
        $this->testdata->student2 = $datagenerator->create_user();
        $this->getDataGenerator()->enrol_user($this->testdata->student2->id, $this->testdata->course2->id, 'student');

        // Create a moodleoverflow with 2 post in each course. (But only if it is available.
        if ($DB->get_record('modules', ['name' => 'moodleoverflow'])) {
            $this->moodleoverflowavailable = true;
            $moodleoverflowgenerator = $datagenerator->get_plugin_generator('mod_moodleoverflow');

            $this->testdata->moodleoverflow1 = $datagenerator->create_module('moodleoverflow', $course1location);
            $this->testdata->mdiscussion1 = $moodleoverflowgenerator->post_to_forum($this->testdata->moodleoverflow1,
                                                                                    $this->testdata->teacher);
            $this->testdata->answer1 = $moodleoverflowgenerator->reply_to_post($this->testdata->mdiscussion1[1],
                                                                               $this->testdata->student1, true);

            $this->testdata->moodleoverflow2 = $datagenerator->create_module('moodleoverflow', $course2location);
            $this->testdata->mdiscussion2 = $moodleoverflowgenerator->post_to_forum($this->testdata->moodleoverflow2,
                                                                                    $this->testdata->teacher);
            $this->testdata->answer2 = $moodleoverflowgenerator->reply_to_post($this->testdata->mdiscussion2[1],
                $this->testdata->student2, true);
        } else {
            $this->moodleoverflowavailable = false;
        }

        // Create a forum with 2 post in each course.
        $forumgenerator = $datagenerator->get_plugin_generator('mod_forum');

        $this->testdata->forum1 = $datagenerator->create_module('forum', $course1location);
        $record = (array)$this->testdata->forum1 + ['forum' => $this->testdata->forum1->id,
                                                    'userid' => $this->testdata->teacher->id, ];
        $this->testdata->fdiscussion1 = (object)$forumgenerator->create_discussion($record);

        $this->testdata->forum2 = $datagenerator->create_module('forum', $course2location);
        $record = (array)$this->testdata->forum2 + ['forum' => $this->testdata->forum2->id,
                                                    'userid' => $this->testdata->teacher->id, ];
        $this->testdata->fdiscussion2 = (object)$forumgenerator->create_discussion($record);
    }


    /**
     * Makes the existing moodleoverflow anonymous.
     * There are 2 types of anonymous moodleoverflows:
     * anonymous = 1, the topic starter is anonymous
     * anonymous = 2, all users are anonymous
     *
     * @param object $moodleoverflow The moodleoverflow that should be made anonymous.
     * @param int $anonymoussetting The type of anonymous moodleoverflow.
     */
    private function make_anonymous($moodleoverflow, $anonymoussetting):void {
        global $DB;
        if ($anonymoussetting == 1 || $anonymoussetting == 2) {
            $moodleoverflow->anonymous = $anonymoussetting;
            $DB->update_record('moodleoverflow', $moodleoverflow);
        } else {
            throw new \Exception('invalid parameter, anonymoussetting should be 1 or 2');
        }
    }

    /**
     * Helper function to get the post events from a certain user.
     * @param object $user The user for whom the events should be collected (townsquareevents.php uses $USER).
     * @return array
     */
    private function get_postevents_from_user($user):array {
        $this->setUser($user);
        $townsquareevents = new townsquareevents();
        return $townsquareevents->townsquare_get_postevents();
    }

    /**
     * Helper function to check if all posts are in the courses of the user.
     * @param array $posts
     * @param array $enrolledcourses
     * @return bool
     */
    private function check_postcourses($posts, $enrolledcourses):bool {
        foreach ($posts as $post) {
            $postcourseid = $post->courseid;

            $enrolledcoursesid = [];
            foreach ($enrolledcourses as $enrolledcourse) {
                $enrolledcoursesid[] = $enrolledcourse->id;
            }

            if (!in_array($postcourseid, $enrolledcoursesid)) {
                return false;
            }
        }
        return true;
    }
}
