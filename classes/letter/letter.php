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

// Import other namespaces.

// Moodle internal check to prevent calling this file from the url.
defined('MOODLE_INTERNAL') || die();

/**
 * Abstract Class to show the latest activities and other new things in moodle
 *
 * A Letter represents one notification, the notification can be:
 * - an activity that is due
 * - a post from a forum that is new
 * - ...
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class letter {
    // Attributes.

    /** @var string Every Letter must save its letter type */
    protected $lettertype;

    /** @var int The course from the letter */
    protected $courseid;

    /** @var string The Name of the plugin */
    protected $modulename;

    /** @var string The content of the letter */
    protected $content;

    /** @var int When the activity was created */
    protected $created;

    // Constructor.

    /**
     * Constructor for a letter
     * @param $courseid
     * @param $modulename
     * @param $content
     * @param $created
     */
    public function __construct($courseid, $modulename, $content, $created) {
        $this->lettertype = 'basic';
        $this->courseid = $courseid;
        $this->modulename = $modulename;
        $this->content = $content;
        $this->created = $created;
    }

    // Getter.

    /**
     * Getter for the letter type
     * @return string
     */
    public function get_lettertype() {
        return $this->lettertype;
    }

    /**
     * Getter for the course.
     * @return int
     */
    public function get_course() {
        return $this->course;
    }

    /**
     * Getter for the module name
     * @return string
     */
    public function get_modulename() {
        return $this->modulename;
    }

    public function get_content() {
        return $this->content;
    }

    /**
     * Getter for the age of the assessment activity.
     * @return int
     */
    public function get_created() {
        return $this->created;
    }
}
