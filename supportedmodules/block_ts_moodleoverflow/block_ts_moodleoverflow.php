<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

use block_townsquare\townsquaresupport;
use block_townsquare\townsquaresupport\townsquaresupportinterface;

global $CFG;
require_once($CFG->dirroot . '/blocks/townsquare/supportedmodules/block_ts_moodleoverflow/locallib.php');

/**
 * Plugin strings are defined here.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ts_moodleoverflow extends block_base implements townsquaresupportinterface {

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init(): void {
        $this->title = get_string('pluginname', 'block_townsquare');
    }

    /**
     * Gets the block contents.
     *
     * @return object|null The block HTML.
     */
    public function get_content(): object {
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = "hallo";
        return $this->content;
    }

    public function get_events(): array {
        $courses = townsquare_get_courses();
        $timestart = townsquare_get_timestart();
        return ts_moodleoverflow_get_events($courses, $timestart);
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats():array {
        return [
            'admin' => false,
            'site-index' => false,
            'course-view' => false,
            'mod' => false,
            'my' => false,
        ];
    }
}
