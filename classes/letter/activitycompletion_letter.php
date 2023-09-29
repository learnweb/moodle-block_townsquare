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

/**
 * Class to show information to the user
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_townsquare\letter;

defined('MOODLE_INTERNAL') || die();

/**
 * Class that represents an activity completion.
 * Subclass from letter.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activitycompletion_letter extends letter {

    /** @var int The id of the author of the post */
    private $author;

    /** @var \moodle_url The url to the activity */
    private $linktoactivity;

    /** @var \moodle_url The url to the course */
    private $linktocourse;

    /**
     * @param $calendarevent object a calendar event with information, for more see classes/townsquareevents.php
     */
    public function __construct($calendarevent) {
        parent::__construct($calendarevent->courseid, $calendarevent->modulename, $calendatevent->name, $calendarevent->timestart);
        $this->lettertype = 'activitycompletion';
        $this->author = $calendarevent->userid;
        $cm = get_coursemodule_from_instance($calendarevent->modulename, $calendarevent->instance);
        $this->linktoactivity = new \moodle_url('/mod/' . $calendarevent->modulename . '/view.php', ['id' => $cm->id]);
        $this->linktocourse = new \moodle_url('/course/view.php', ['id' => $calendarevent->courseid]);
    }

    // Getter.

    /**
     * Overrides function from superclass.
     * @return string
     */
    public function get_lettertype() {
        return $this->lettertype;
    }

    /**
     * @return int
     */
    public function get_author() {
        return $this->author;
    }

    /**
     * @return \moodle_url
     */
    public function get_linktoactivity() {
        return $this->linktoactivity;
    }

    /**
     * @return \moodle_url
     */
    public function get_linktocourse() {
        return $this->linktocourse;
    }
}
