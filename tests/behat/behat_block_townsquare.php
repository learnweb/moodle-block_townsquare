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

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\DriverException;
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
     * Prepare a basic background for townsquare features.
     * @Given /^I prepare a townsquare feature background$/
     *
     * @return void
     */
    public function i_prepare_townsquare_background() {
        global $DB;
        // Create users.
        $this->execute('behat_data_generators::the_following_entities_exist', ['users', new TableNode([
            ['username', 'firstname', 'lastname', 'email', 'idnumber'],
            ['student1', 'Tamaro', 'Walter', 'student1@example.com', 'S1'],
        ])]);

        // Create courses.
        $time = time();
        $starttime = $time - 86400;
        $endtime = $time + 15778458;
        $this->execute('behat_data_generators::the_following_entities_exist', ['courses', new TableNode([
            ['fullname', 'shortname', 'category', 'startdate', 'enddate', 'enablecompletion', 'showcompletionconditions'],
            ['Course 1', 'C1', '0', $starttime, $endtime, '1', '1'],
            ['Course 2', 'C2', '0', $starttime, $endtime, '1', '1'],
            ['Course 3', 'C3', '0', $starttime, $endtime, '1', '1'],
        ])]);

        // Enroll users.
        $this->execute('behat_data_generators::the_following_entities_exist', ['course enrolments', new TableNode([
            ['user', 'course', 'role'],
            ['student1', 'C1', 'student'],
            ['student1', 'C2', 'student'],
            ['student1', 'C3', 'student'],
        ])]);

        // Create activities.
        $this->execute('behat_data_generators::the_following_entities_exist', ['activities', new TableNode([
            ['activity', 'course', 'idnumber', 'name', 'intro', 'timeopen', 'duedate'],
            ['assign', 'C1', '10', 'Test assign 1', 'Assign due in 2 months', $time - 172800, $time + 86400],
            ['assign', 'C2', '11', 'Test assign 2', 'Assign due in 4 days', $time - 172800, $time + 345600],
            ['assign', 'C3', '12', 'Test assign 3', 'Assign due in 6 days', $time - 172800, $time + 518400],
        ])]);

        // Create Townsquare.
        $this->execute('behat_data_generators::the_following_entities_exist', ['blocks', new TableNode([
            ['blockname', 'contextlevel', 'reference', 'pagetypepattern', 'defaultregion'],
            ['townsquare', 'System', '1', 'my-index', 'content'],
        ])]);

        // Deactivate the timeline and calendar blocks.
        $blocks = ['timeline', 'calendar_month'];
        foreach ($blocks as $blockname) {
            if ($block = $DB->get_record('block', ['name' => $blockname])) {
                $block->visible = 0;
                $DB->update_record('block', $block);
            }
        }
    }

    /**
     * Checks if elements can be seen in the townsquare block.
     *
     * @Given /^I should "(?P<text_string>(?:[^"]|\\")*)" see in townsquare the elements:$/
     * @param string $text Type "not" when elements should not be visible, "" if they should be visible
     * @param TableNode $data
     * @return void
     * @throws DriverException
     */
    #[\core\attribute\example('I should "" see in townsquare the elements:
        | Test assign 1 | Test assign 2 |')]
    public function i_see_elements_in_townsquare($text, TableNode $data): void {
        $elements = $data->getRow(0);
        foreach ($elements as $element) {
            if ($text == "not") {
                $this->execute('behat_general::assert_element_not_contains_text', [$element, 'Town Square', 'block']);
            } else {
                $this->execute('behat_general::assert_element_contains_text', [$element, 'Town Square', 'block']);
            }
        }
    }

    /**
     * Follows a sequence of clicks elements inside the townsquare block.
     *
     * @Given /^I click in townsquare on "(?P<text_string>(?:[^"]|\\")*)" type:$/
     * @param string $type Type of element like "text", "checkbox" or "button".
     * @param TableNode $data
     * @return void
     * @throws DriverException
     */
    #[\core\attribute\example('I click in townsquare on:
        | Time filter | Next month | Last two days |')]
    public function i_click_in_townsquare_on(string $type, TableNode $data): void {
        $elements = $data->getRow(0);
        foreach ($elements as $element) {
            $this->execute('behat_general::i_click_on', [$element, $type]);
        }
    }
}
