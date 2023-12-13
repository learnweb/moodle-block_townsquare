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
 *
 * This class is used for the basic letter type and is the top-class for more specific letters..
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class letter {

    // Attributes.

    /** @var int an ID to identify every content in townsquare */
    protected int $contentid;

    /** @var string Every Letter must save its letter type */
    protected string $lettertype;

    /** @var int course module id of the content module */
    protected int $cmid;

    /** @var int The course from the letter */
    protected int $courseid;

    /** @var string The name of the course */
    protected string $coursename;

    /** @var string The Name of the plugin */
    protected string $modulename;

    /** @var string The content of the letter */
    protected string $content;

    /** @var int Timestamp When the activity was created */
    protected int $created;

    /** @var bool variable for the mustache template */
    public bool $isbasic = true;

    /** @var moodle_url Link to the course */
    protected moodle_url $linktocourse;

    /** @var moodle_url The url to the activity */
    protected moodle_url $linktoactivity;

    // Constructor.

    /**
     * Constructor for a letter
     *
     * @param int $contentid        internal ID in the townsquare block.
     * @param int $courseid         Course ID from where the content comes from.
     * @param string $modulename    Name of the module/activity.
     * @param mixed $content        The content that will be showed in the letter.
     * @param int $created          Timestamp of creation.
     * @param int $cmid             Course module id of the content module.
     */
    public function __construct($contentid, $courseid, $modulename, $content, $created, $cmid) {
        $this->contentid = $contentid;
        $this->lettertype = 'basic';
        $this->courseid = $courseid;
        $this->cmid = $cmid;
        $this->coursename = get_course($courseid)->fullname;
        $this->modulename = $modulename;
        $this->content = $content;
        $this->created = $created;
        $this->linktocourse = new moodle_url('/course/view.php', ['id' => $this->courseid]);
        $this->linktoactivity = new moodle_url('/mod/' . $modulename . '/view.php', ['id' => $cmid]);
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
            'linktoactivity' => $this->linktoactivity->out(),
        ];
    }
}
