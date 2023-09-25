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
abstract class letter {
    // Attributes.

    /** @var int The course from the letter */
    protected $courseid;

    /** @var string The Name of the plugin */
    protected $modulename;

    /** @var int When the activity was created */
    protected $created;

    /** @var \moodle_url */
    protected $linktomodule;

    // Constructor.

    /**
     * Constructor for a letter
     * @param $course
     * @param $coursemoduleid
     * @param $age
     */
    public function __construct($courseid, $modulename, $created) {
        $this->courseid = $courseid;
        $this->modulename = $modulename;
        $this->created = $created;
    }
    
    // Getter.

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
        return $this->coursemoduleid;
    }

    /**
     * Getter for the age of the assessment activity.
     * @return int
     */
    public function get_created() {
        return $this->created;
    }
}

// Subclasses.
