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
     * Test, if the right letters are created.
     * @return void
     */
    public function test_letters(): void {
        // Set an logged in user.
        $this->setUser($this->testdata->teacher);

        // Create the controller and get the townsquareevents.
        $controller = new contentcontroller();
        $content = $controller->build_content();
        $events = array_values($controller->events);

        // Check the lettertype for each letter.
        $result = true;
        $contentlength = 0;

        foreach ($content as $lettergroup) {
            foreach ($lettergroup->letters as $letter) {
                // The letters are sorted by their distance to now. The content id is the index of the event of the letter.
                $eventtype = $events[$letter['contentid']]->eventtype;
                $postcheck = $this->check_two_params($eventtype, 'post', $letter['lettertype'], 'post');
                $completioncheck = $this->check_two_params(
                    $eventtype,
                    'expectcompletionon',
                    $letter['lettertype'],
                    'activitycompletion'
                );
                $basiccheck = ($eventtype != 'post' && $eventtype != 'expectcompletionon') && $letter['lettertype'] == 'basic';

                // If one of the checks fails there is a problem while creating the letters.
                if (!$postcheck && !$completioncheck && !$basiccheck) {
                    $result = false;
                    break;
                }
                $contentlength++;
            }
        }

        if ($this->modoverflowinstalled) {
            $this->assertEquals(5, $contentlength);
        } else {
            $this->assertEquals(4, $contentlength);
        }
        $this->assertEquals(true, $result);
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

        $content = (new contentcontroller())->build_content();

        // The expected values are calculated with PHP, independent of the functions of the controller.
        $usertimezone = new \DateTimeZone($timezone);
        $today = new \DateTime('today', $usertimezone);
        $labels = [
            $today->format('d.m.Y') => get_string('today', 'calendar'),
            (clone $today)->modify('+1 day')->format('d.m.Y') => get_string('tomorrow', 'calendar'),
            (clone $today)->modify('-1 day')->format('d.m.Y') => get_string('yesterday', 'calendar'),
        ];

        foreach ($content as $group) {
            $this->assertCount($group->count, $group->letters);
            $this->assertEquals($labels[$group->day] ?? '', $group->daylabel);
            $this->assertEquals($group->daystart < $today->getTimestamp(), $group->ispast);
            foreach ($group->letters as $letter) {
                $created = (new \DateTime('@' . $letter['createdtimestamp']))->setTimezone($usertimezone);
                $this->assertEquals($created->format('d.m.Y'), $group->day);
                $this->assertEquals((clone $created)->setTime(0, 0)->getTimestamp(), $group->daystart);
            }
        }

        // The letters closest to now come first: today and the upcoming days with the earliest letter first, then the
        // past days with the latest letter first.
        $timestamps = array_merge(...array_map(fn($group) => array_column($group->letters, 'createdtimestamp'), $content));
        $upcoming = array_filter($timestamps, fn($timestamp) => $timestamp >= $today->getTimestamp());
        $past = array_filter($timestamps, fn($timestamp) => $timestamp < $today->getTimestamp());
        sort($upcoming);
        rsort($past);
        $this->assertSame(array_merge($upcoming, $past), $timestamps);

        // The posts were written today and the assignment is due tomorrow.
        $daylabels = array_column($content, 'daylabel');
        $this->assertContains(get_string('today', 'calendar'), $daylabels);
        $this->assertContains(get_string('tomorrow', 'calendar'), $daylabels);
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

        // Check if mod_moodleoverflow and the townsquare support plugin are installed.
        if (
            $DB->get_record('modules', ['name' => 'moodleoverflow', 'visible' => 1]) &&
            array_key_exists('moodleoverflow', \core_plugin_manager::instance()->get_plugins_of_type('townsquareexpansion'))
        ) {
            $this->modoverflowinstalled = true;
            $modoverflowgenerator = $datagenerator->get_plugin_generator('mod_moodleoverflow');
            $this->testdata->moodleoverflow = $datagenerator->create_module(
                'moodleoverflow',
                ['course' => $this->testdata->course->id]
            );
            $this->testdata->mdiscussion = $modoverflowgenerator->post_to_forum(
                $this->testdata->moodleoverflow,
                $this->testdata->teacher
            );
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
