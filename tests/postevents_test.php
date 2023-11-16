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
 * PHPUnit tests for testing the process of collecting post events.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \townsquareevents::townsquare_get_postevents()
 */
class postevents_test extends \advanced_testcase {

    // Attributes.

    /** @var stdClass The data that will be used for testing.
     * This Class contains the test data:
     * - course1, course2
     * - forum1, forum2 (with one post each)
     * - moodleoverflow1, moodleoverflow2 (With two post each)
     * - teacher (write one post in each forum and moodleoverflow)
     * - student1, student2 (write one post in their moodleoverflow)
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
     * Test, if post events are sorted correctly.
     * Post should be sorted by the time they were created in descending order (newest post first).
     * @return void
     */
    public function test_sortorder() : void {
        // Set the teacher as the current logged in user.
        $this->setUser($this->testdata->teacher);

        // Get the current post events.
        $townsquareevents = new townsquareevents();
        $posts = $townsquareevents->townsquare_get_postevents();

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
     * Test, if the post events are processed correctly if one modules is not installed.
     * @return void
     */
    public function test_modules() : void {
        global $DB;
        // Test case 1: disable moodleoverflow.
        $DB->delete_records('modules', ['name' => 'moodleoverflow']);

        // Set the teacher as the current logged in user and get the post events.
        $this->setUser($this->testdata->teacher);
        $townsquareevents = new townsquareevents();
        $posts = $townsquareevents->townsquare_get_postevents();

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

        // Test case 2: disable forum (But add the moodleoverflow module again).
        $moodleoverflowmodule = ['name' => 'moodleoverflow', 'cron' => 0, 'lastcron' => 0, 'search' => 0, 'visible' => 1];
        $DB->insert_record('modules', (object)$moodleoverflowmodule);
        $DB->delete_records('modules', ['name' => 'forum']);

        // Get the current post events.
        $townsquareevents = new townsquareevents();
        $posts = $townsquareevents->townsquare_get_postevents();


        $result = true;
        // Check if there are no more moodleoverflow posts.
        foreach ($posts as $post) {
            if ($post->modulename == 'forum') {
                $result = false;
            }
        }

        // Two Checks: The number of posts (there are 4 moodleoverflow posts).
        $this->assertEquals(4, count($posts));
        $this->assertEquals(true, $result);
    }

    /**
     * Test, if the users see only posts of their courses.
     * @return void
     */
    public function test_course() : void {
        // Testcase 1: Post for the teacher.
        // Set the teacher as the current logged in user and get the current posts.
        $this->setUser($this->testdata->teacher);
        $townsquareevents = new townsquareevents();
        $posts = $townsquareevents->townsquare_get_postevents();

        // Check if the teacher sees only posts of his courses.
        $result = $this->check_postcourses($posts, enrol_get_all_users_courses($this->testdata->teacher->id));

        // Two Checks: Is the number of posts correct (no post is missing) and is every post in the course of the teacher.
        $this->assertEquals(6, count($posts));
        $this->assertEquals(true, $result);

        // Testcase 2: Post for the first student.
        $this->setUser($this->testdata->student1);
        $townsquareevents = new townsquareevents();
        $posts = $townsquareevents->townsquare_get_postevents();

        $result = $this->check_postcourses($posts, enrol_get_all_users_courses($this->testdata->student1->id));

        $this->assertEquals(3, count($posts));
        $this->assertEquals(true, $result);

        // Testcase 3: Post for the second student.
        $this->setUser($this->testdata->student2);
        $townsquareevents = new townsquareevents();
        $posts = $townsquareevents->townsquare_get_postevents();

        $result = $this->check_postcourses($posts, enrol_get_all_users_courses($this->testdata->student2->id));

        $this->assertEquals(3, count($posts));
        $this->assertEquals(true, $result);
    }

    /**
     * Test, if data in moodleoverflow posts is processed correctly when the moodleoverflow is anonymous.
     * @return void
     */
    public function test_anonymous() : void {
        // Set the teacher as the current logged in user.
        $this->setUser($this->testdata->teacher);

        // Set the first moodleoverflow to partially anonymous and the second to fully anonymous.
        $this->make_anonymous($this->testdata->moodleoverflow1, 1);
        $this->make_anonymous($this->testdata->moodleoverflow2, 2);

        // Get the current post events.
        $townsquareevents = new townsquareevents();
        $posts = $townsquareevents->townsquare_get_postevents();

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

        // Test Case 1: The teacherpost should be anonymous and the studentpost should not be anonymous (partial anonymous).
        $this->assertEquals(true, $firstteacherpost->anonymous);
        $this->assertEquals(false, $firststudentpost->anonymous);

        // Test Case 2: The teacherpost and studentpost should be anonymous (fully anonymous).
        $this->assertEquals(true, $secondteacherpost->anonymous);
        $this->assertEquals(true, $secondstudentpost->anonymous);
    }

    /*public function test_numberofposts() : void {

    }*/

    // Helper functions.

    /**
     * Helper function that creates:
     * - two courses with a forum and a moodleoverflow
     * - a teacher, who creates a post in each forum and moodleoverflow.
     * - a student in each course
     *
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
        $this->testdata->teacher = $datagenerator->create_user();
        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course1->id, 'teacher');
        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course2->id, 'teacher');

        // Create two students.
        $this->testdata->student1 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->testdata->student1->id, $this->testdata->course1->id, 'student');
        $this->testdata->student2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->testdata->student2->id, $this->testdata->course2->id, 'student');

        // Create a moodleoverflow with 2 post in each course.
        $moodleoverflowgenerator = $datagenerator->get_plugin_generator('mod_moodleoverflow');

        $this->testdata->moodleoverflow1 = $datagenerator->create_module('moodleoverflow', $course1location);
        $this->testdata->mdiscussion1 = $moodleoverflowgenerator->post_to_forum($this->testdata->moodleoverflow1,
                                                                                $this->testdata->teacher);
        $this->testdata->mpost1 = $DB->get_record('moodleoverflow_posts', ['id' => $this->testdata->mdiscussion1[0]->firstpost]);
        $this->testdata->answer1 = $moodleoverflowgenerator->reply_to_post($this->testdata->mdiscussion1[1],
                                                                           $this->testdata->student1, true);

        $this->testdata->moodleoverflow2 = $datagenerator->create_module('moodleoverflow', $course2location);
        $this->testdata->mdiscussion2 = $moodleoverflowgenerator->post_to_forum($this->testdata->moodleoverflow2,
                                                                                $this->testdata->teacher);
        $this->testdata->mpost2 = $DB->get_record('moodleoverflow_posts', ['id' => $this->testdata->mdiscussion2[0]->firstpost]);
        $this->testdata->answer2 = $moodleoverflowgenerator->reply_to_post($this->testdata->mdiscussion2[1],
                                                                           $this->testdata->student2, true);
        // Create a forum with 2 post in each course.
        $forumgenerator = $datagenerator->get_plugin_generator('mod_forum');

        $this->testdata->forum1 = $datagenerator->create_module('forum', $course1location);
        $record = (array)$this->testdata->forum1 + ['forum' => $this->testdata->forum1->id, 'userid' => $this->testdata->teacher->id];
        $this->testdata->fdiscussion1 = (object)$forumgenerator->create_discussion($record);
        $this->testdata->fpost1 = $DB->get_record('forum_posts', ['id' => $this->testdata->fdiscussion1->id]);

        $this->testdata->forum2 = $datagenerator->create_module('forum', $course2location);
        $record = (array)$this->testdata->forum2 + ['forum' => $this->testdata->forum2->id, 'userid' => $this->testdata->teacher->id];
        $this->testdata->fdiscussion2 = (object)$forumgenerator->create_discussion($record);
        $this->testdata->fpost2 = $DB->get_record('forum_posts', ['id' => $this->testdata->fdiscussion2->id]);
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
    private function make_anonymous($moodleoverflow, $anonymoussetting) {
        global $DB;
        if ($anonymoussetting == 1 || $anonymoussetting == 2) {
            $moodleoverflow->anonymous = $anonymoussetting;
            $DB->update_record('moodleoverflow', $moodleoverflow);
        } else {
            throw new \Exception('invalid parameter, anonymoussetting should be 1 or 2');
        }
    }

    /**
     * Helper function to check if all posts are in the courses of the user.
     * @param $posts
     * @param $enrolledcourses
     * @return bool
     */
    private function check_postcourses($posts, $enrolledcourses) {
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
