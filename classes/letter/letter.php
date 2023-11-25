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
 * Class to show content to the user
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_townsquare\letter;

use moodle_url;

/**
 * Class that represents an unspecific type of content.
 * This class is used for the basic letter type and is the top-class for more specific letters..
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class letter {

    // Attributes.

    /** @var int an ID to identify every content in townsquare */
    protected $contentid;

    /** @var string Every Letter must save its letter type */
    protected $lettertype;

    /** @var int The course from the letter */
    protected $courseid;

    /** @var string The name of the course */
    protected $coursename;

    /** @var string The Name of the plugin */
    protected $modulename;

    /** @var string The content of the letter */
    protected $content;

    /** @var int Timestamp When the activity was created */
    protected $created;

    /** @var bool variable for the mustache template */
    public $isbasic = true;

    /** @var moodle_url Link to the course */
    protected $linktocourse;

    // Constructor.

    /**
     * Constructor for a letter
     *
     * @param int $contentid        internal ID in the townsquare block.
     * @param int $courseid         Course ID from where the content comes from.
     * @param string $modulename    Name of the module/activity.
     * @param mixed $content        The content that will be showed in the letter.
     * @param int $created          Timestamp of creation.
     */
    public function __construct($contentid, $courseid, $modulename, $content, $created) {
        $this->contentid = $contentid;
        $this->lettertype = 'basic';
        $this->courseid = $courseid;
        $this->coursename = get_course($courseid)->fullname;
        $this->modulename = $modulename;
        $this->content = $content;
        $this->created = $created;
        $this->linktocourse = new moodle_url('/course/view.php', ['id' => $this->courseid]);
    }

    // Functions.

    /**
     * Export function for the mustache template.
     * @return array
     */
    public function export_letter() {
        // Change the timestamp to a date.
        $date = date('d.m.Y', $this->created);

        return [
            'contentid' => $this->contentid,
            'lettertype' => $this->lettertype,
            'isbasic' => $this->isbasic,
            'courseid' => $this->courseid,
            'coursename' => $this->coursename,
            'modulename' => $this->modulename,
            'content' => $this->content,
            'created' => $date,
            'linktocourse' => $this->linktocourse->out(),
        ];
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
     * Getter for the course id.
     * @return int
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Getter for the course name.
     * @return string
     */
    public function get_coursename() {
        return $this->coursename;
    }

    /**
     * Getter for the module name
     * @return string
     */
    public function get_modulename() {
        return $this->modulename;
    }

    /**
     * Getter for the content
     * @return string
     */
    public function get_content() {
        return $this->content;
    }

    /**
     * Getter for the link to the course
     * @return moodle_url
     */
    public function get_linktocourse() {
        return $this->linktocourse;
    }

    /**
     * Getter for the age of the activity.
     * @return int
     */
    public function get_created() {
        return $this->created;
    }
}
