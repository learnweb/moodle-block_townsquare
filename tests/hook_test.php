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

use block_townsquare\hook\extend_content;
use block_townsquare\item\deadline_item;
use block_townsquare\item\post_item;
use block_townsquare\local\service\items;
use block_townsquare\output\content;
use block_townsquare\output\letter\deadline_letter;
use block_townsquare\output\letter\post_letter;
use moodle_url;
use stdClass;

/**
 * PHPUnit tests for the hook that lets plugins show their posts and deadlines in Town Square.
 *
 * @package   block_townsquare
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \block_townsquare\hook\extend_content
 * @covers \block_townsquare\local\service\items
 */
final class hook_test extends \advanced_testcase {
    /** @var stdClass The data that will be used for testing.
     * - course1 with a visible and a hidden page, the student is enrolled
     * - course2 with a page, the student is not enrolled
     * - a student
     */
    private stdClass $testdata;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->testdata = new stdClass();
        $this->testdata->course1 = $generator->create_course();
        $this->testdata->course2 = $generator->create_course();
        $this->testdata->student = $generator->create_user();
        $generator->enrol_user($this->testdata->student->id, $this->testdata->course1->id, 'student');

        $this->testdata->page = $generator->create_module('page', ['course' => $this->testdata->course1->id]);
        $this->testdata->hiddenpage = $generator->create_module('page', ['course' => $this->testdata->course1->id]);
        set_coursemodule_visible($this->testdata->hiddenpage->cmid, 0);
        $this->testdata->otherpage = $generator->create_module('page', ['course' => $this->testdata->course2->id]);

        $this->setUser($this->testdata->student);
    }

    /**
     * Test, if the hook gets the user, the courses and the time span of Town Square.
     * @return void
     */
    public function test_hook_context(): void {
        $hooks = [];
        $this->redirectHook(extend_content::class, function (extend_content $hook) use (&$hooks) {
            $hooks[] = $hook;
        });

        $items = new items();
        $items->get();

        $this->assertCount(1, $hooks);
        $this->assertEquals($this->testdata->student->id, $hooks[0]->userid);
        $this->assertEquals([$this->testdata->course1->id], $hooks[0]->courseids);
        $this->assertEquals($items->timestart, $hooks[0]->timestart);
        $this->assertEquals($items->timeend, $hooks[0]->timeend);
    }

    /**
     * Test, if added posts and deadlines are shown as letters, as the plugin provides them.
     * @return void
     */
    public function test_letters(): void {
        global $PAGE;
        $page = $this->testdata->page;
        $pictureurl = new moodle_url('/pix/anonymous.png');
        $this->redirectHook(extend_content::class, function (extend_content $hook) use ($page, $pictureurl) {
            $hook->add_post(new post_item(
                courseid: $page->course,
                cmid: $page->cmid,
                discussionsubject: 'A question',
                message: '<p>The message</p>',
                time: time() - HOURSECS,
                url: new moodle_url('/mod/page/view.php', ['id' => $page->cmid]),
                authorname: 'Anonymous student',
                pictureurl: $pictureurl,
            ));
            $hook->add_deadline(new deadline_item(
                courseid: $page->course,
                cmid: $page->cmid,
                name: 'The page should be read',
                time: time() + DAYSECS,
            ));
        });

        $output = $PAGE->get_renderer('core');
        $items = new items();
        $letters = (new content($items->get(), $items->get_lastupdate()))->get_letters();
        $posts = array_values(array_filter($letters, fn($letter) => $letter instanceof post_letter));
        $deadlines = array_values(array_filter($letters, fn($letter) => $letter instanceof deadline_letter));
        $posts = array_map(fn($letter) => $letter->export_for_template($output), $posts);
        $deadlines = array_map(fn($letter) => $letter->export_for_template($output), $deadlines);

        // The post is shown as it is: without a link to the author and with the picture of the plugin.
        $this->assertCount(1, $posts);
        $this->assertEquals('Anonymous student', $posts[0]['authorname']);
        $this->assertNull($posts[0]['linktoauthor']);
        $this->assertEquals($pictureurl->out(false), $posts[0]['authorpictureurl']);
        $this->assertEquals('<p>The message</p>', $posts[0]['message']);
        $this->assertEquals($page->name, $posts[0]['instancename']);

        $this->assertCount(1, $deadlines);
        $this->assertEquals('The page should be read', $deadlines[0]['content']);
    }

    /**
     * Test, if items of hidden activities, of other courses and outside of the time span are not shown.
     * @return void
     */
    public function test_items_are_checked(): void {
        $testdata = $this->testdata;
        $items = new items();
        $timestart = $items->timestart;
        $timeend = $items->timeend;

        $this->redirectHook(extend_content::class, function (extend_content $hook) use ($testdata, $timestart, $timeend) {
            $post = fn($cm, $name, $time) => new post_item(
                courseid: $cm->course,
                cmid: $cm->cmid,
                discussionsubject: $name,
                message: $name,
                time: $time,
                url: new moodle_url('/mod/page/view.php', ['id' => $cm->cmid]),
                authorname: 'Author',
            );
            $deadline = fn($cm, $name, $time) => new deadline_item($cm->course, $cm->cmid, $name, $time);

            $hook->add_post($post($testdata->page, 'shown post', time()));
            $hook->add_post($post($testdata->hiddenpage, 'hidden activity', time()));
            $hook->add_post($post($testdata->otherpage, 'other course', time()));
            $hook->add_post($post($testdata->page, 'too old', $timestart - 1));
            $hook->add_deadline($deadline($testdata->page, 'shown deadline', time() + DAYSECS));
            $hook->add_deadline($deadline($testdata->hiddenpage, 'hidden activity', time() + DAYSECS));
            $hook->add_deadline($deadline($testdata->otherpage, 'other course', time() + DAYSECS));
            $hook->add_deadline($deadline($testdata->page, 'too late', $timeend + 1));
        });

        // The items are sorted, the latest first.
        $names = array_map(fn($item) => $item instanceof post_item ? $item->message : $item->name, $items->get());
        $this->assertEquals(['shown deadline', 'shown post'], $names);
    }
}
