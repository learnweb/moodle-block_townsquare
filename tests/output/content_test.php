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

namespace block_townsquare\output;

use block_townsquare\output\letter\deadline_letter;
use block_townsquare\output\letter\post_letter;
use block_townsquare\townsquareevents;
use stdClass;

/**
 * Unit tests for the block_townsquare.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * PHPUnit tests for the content of Town Square: the letters and the letter groups.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_townsquare\output\content
 * @covers \block_townsquare\output\lettergroup
 */
final class content_test extends \advanced_testcase {
    // Attributes.

    /** @var object The data that will be used for testing
     * This Class contains:
     * - A Course
     * - A User
     * - One forum with one post
     * - One assignment with an activity completion
     */
    private $testdata;

    // Construct functions.

    public function setUp(): void {
        parent::setUp();
        $this->testdata = new \stdClass();
        $this->resetAfterTest();
        $this->helper_course_set_up();
    }

    public function tearDown(): void {
        $this->testdata = null;
        parent::tearDown();
    }

    // Tests.

    /**
     * Test, if the right letters are created: one for each post and one for each group of deadlines.
     * @return void
     */
    public function test_letters(): void {
        // Set an logged in user.
        $this->setUser($this->testdata->teacher);

        // The items: the post, the due date, the expected completion (both tomorrow) and the grading due date.
        $this->assertCount(4, (new townsquareevents())->get_items());

        // The post has its own letter. The due date and the expected completion of the assignment are at the same time,
        // so they are grouped in one letter.
        $letters = (new content())->get_letters();
        $posts = array_filter($letters, fn($letter) => $letter instanceof post_letter);
        $deadlines = array_filter($letters, fn($letter) => $letter instanceof deadline_letter);
        $this->assertCount(3, $letters);
        $this->assertCount(1, $posts);
        $this->assertCount(2, $deadlines);

        $output = $this->get_renderer();
        $deadlines = array_map(fn($letter) => $letter->export_for_template($output), $deadlines);
        $grouped = array_values(array_filter($deadlines, fn($letter) => $letter['grouped']));
        $this->assertCount(1, $grouped);
        $names = array_column($grouped[0]['deadlines'], 'name');
        $assignname = $this->testdata->assignment->name;
        $this->assertEquals([
            get_string('calendardue', 'assign', $assignname),
            get_string('completionexpectedfor', 'completion', (object) ['instancename' => $assignname]),
        ], $names);
    }

    /**
     * Test, if the letters are grouped by the days in the timezone of the user, if the days are labeled and if the
     * letters closest to now come first.
     * @return void
     */
    public function test_days(): void {
        global $DB;

        // The timezones of the server (UTC+14) and the user (UTC-11) are 25 hours apart, so every letter is on another
        // day for the server than for the user. Both have no daylight saving time, so that the assignment that is due in
        // 24 hours is always due tomorrow.
        $this->setTimezone('Pacific/Kiritimati', 'Pacific/Kiritimati');
        $timezone = 'Pacific/Pago_Pago';
        $DB->set_field('user', 'timezone', $timezone, ['id' => $this->testdata->teacher->id]);
        $this->setUser($DB->get_record('user', ['id' => $this->testdata->teacher->id]));

        $content = new content();
        $lettergroups = $content->group_by_day($content->get_letters());
        $output = $this->get_renderer();
        $exported = array_map(fn($lettergroup) => (object) $lettergroup->export_for_template($output), $lettergroups);

        // The expected values are calculated with PHP, independent of the functions of the content.
        $usertimezone = new \DateTimeZone($timezone);
        $today = new \DateTime('today', $usertimezone);
        $labels = [
            $today->format('d.m.Y') => get_string('today', 'calendar'),
            (clone $today)->modify('+1 day')->format('d.m.Y') => get_string('tomorrow', 'calendar'),
            (clone $today)->modify('-1 day')->format('d.m.Y') => get_string('yesterday', 'calendar'),
        ];

        foreach ($lettergroups as $index => $lettergroup) {
            $group = $exported[$index];
            $this->assertCount($group->count, $lettergroup->get_letters());
            $this->assertEquals($labels[$group->day] ?? '', $group->daylabel);
            $this->assertEquals($lettergroup->get_daystart() < $today->getTimestamp(), $group->ispast);
            foreach ($lettergroup->get_letters() as $letter) {
                $created = (new \DateTime('@' . $letter->get_time()))->setTimezone($usertimezone);
                $this->assertEquals($created->format('d.m.Y'), $group->day);
                $this->assertEquals((clone $created)->setTime(0, 0)->getTimestamp(), $lettergroup->get_daystart());
            }
        }

        // The letters closest to now come first: today and the upcoming days with the earliest letter first, then the
        // past days with the latest letter first.
        $letters = array_merge(...array_map(fn($lettergroup) => $lettergroup->get_letters(), $lettergroups));
        $timestamps = array_map(fn($letter) => $letter->get_time(), $letters);
        $upcoming = array_filter($timestamps, fn($timestamp) => $timestamp >= $today->getTimestamp());
        $past = array_filter($timestamps, fn($timestamp) => $timestamp < $today->getTimestamp());
        sort($upcoming);
        rsort($past);
        $this->assertSame(array_merge($upcoming, $past), $timestamps);

        // The posts were written today and the assignment is due tomorrow.
        $daylabels = array_column($exported, 'daylabel');
        $this->assertContains(get_string('today', 'calendar'), $daylabels);
        $this->assertContains(get_string('tomorrow', 'calendar'), $daylabels);
    }

    // Helper functions.

    /**
     * Helper function that sets up the testdata.
     * @return void
     */
    private function helper_course_set_up(): void {
        // Declare generators.
        $datagenerator = $this->getDataGenerator();
        $forumgenerator = $datagenerator->get_plugin_generator('mod_forum');

        // Create a course and a user.
        $this->testdata->course = $datagenerator->create_course(['enablecompletion' => 1]);
        $this->testdata->teacher = $datagenerator->create_user();
        $datagenerator->enrol_user($this->testdata->teacher->id, $this->testdata->course->id, 'teacher');

        // Create a forum with a post.
        $this->testdata->forum = $datagenerator->create_module('forum', ['course' => $this->testdata->course->id]);
        $record = (array)$this->testdata->forum + ['forum' => $this->testdata->forum->id, 'userid' => $this->testdata->teacher->id];
        $this->testdata->fdiscussion = (object)$forumgenerator->create_discussion($record);

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
        $options = ['completion' => COMPLETION_TRACKING_MANUAL, 'completionexpected' => $duedate];

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
     * Helper function to get the renderer that renders the letters.
     * @return \renderer_base
     */
    private function get_renderer(): \renderer_base {
        global $PAGE;
        return $PAGE->get_renderer('core');
    }
}
