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
 * Steps definitions related with the townsquare block.
 *
 * @package    block_townsquare
 * @category   test
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;

/**
 * townsquare-related steps definitions.
 *
 * @package    block_townsquare
 * @category   test
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_townsquare extends behat_base {

    /**
     * Adds an activity completion event.
     * @Given /^I add a townsquare completion event to "(?P<course>(?:[^"]|\\")*)"$/
     * @param string $coursename the course short name.
     */
    public function i_add_an_townsquare_completion_event(string $coursename) {
        global $DB;
        $generator = new testing_data_generator();
        $course = $DB->get_record('course', ['shortname' => $coursename]);
        $options = [
            'completion' => COMPLETION_TRACKING_MANUAL,
            'completionexpected' => time() + 604800,
        ];

        $assignrecord = [
            'course' => $course->id,
            'courseid' => $course->id,
            'duedate' => time() + 604800,
            'allowsubmissionsfromdate' => time() - 604800,
            'gradingduedate' => time() + 604860,
        ];
        $generator->create_module('assign', $assignrecord, $options);
    }

    /**
     * Deactivates unnecessary dashboard blocks for testing.
     * @Given /^I deactivate unnecessary dashboard blocks$/
     *
     * @return void
     * @throws dml_exception
     */
    public function i_deactivate_unnecessary_dashboard_blocks() {
        global $DB;

        // Deactivate the timeline and calendar blocks.
        $blocks = ['timeline', 'calendar_month'];
        foreach ($blocks as $blockname) {
            if ($block = $DB->get_record('block', ['name' => $blockname])) {
                $block->visible = 0;
                $DB->update_record('block', $block);
            }
        }
    }
}
