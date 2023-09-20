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
    protected $course;

    /** @var string The ID of the course module */
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
    public function __construct($course, $modulename, $created) {
        $this->course = $course;
        $this->modulename = $modulename;
        $this->created = $created;
    }

    /**
     * Getter for the course.
     * @return int
     */
    public function get_course() {
        return $this->course;
    }

    /**
     * Getter for the coursemodule id.
     * @return int
     */
    public function get_coursemoduleid() {
        return $this->coursemoduleid;
    }

    /**
     * Getter for the age of the assessment activity.
     * @return int
     */
    public function get_age() {
        return $this->age;
    }
}

// Subclasses.

/**
 * Class to represent an assessment activity
 *
 * An assessment activity is:
 * - an assignment
 * - a quiz
 * - a workshop
 *
 * An assessment activity has an opening and a due/closing date.
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assessment_letter extends letter {

    // Attributes.

    /** @var int when the activity started */
    protected $opendate;

    /** @var int when the activity is due */
    protected $closedate;

    /**
     * @param $course
     * @param $modulename
     * @param $age
     * @param $opendate
     * @param $duedate
     */
    public function __construct($course, $modulename, $age, $opendate = false, $closedate = false) {
        parent::__construct($course, $modulename, $age);
        $this->opendate = $opendate;
        $this->closedate = $closedate;
    }

    /**
     * Getter for the opendate.
     * @return false|mixed
     */
    protected function get_opendate() {
        return $this->opendate;
    }

    /**
     * Getter for the closedate.
     * @return false|int|mixed
     */
    protected function get_closedate() {
        return $this->closedate;
    }
}

/**
 * Class to show posts from a forum.
 *
 * A forum post has an author and a link to it.
 */
class forum_letter extends letter {

    /** @var int the id of the author */
    protected $author;
    protected $linktoauthor;
}
