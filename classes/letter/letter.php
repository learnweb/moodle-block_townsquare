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

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/blocks/townsquare/locallib.php');

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

    /** @var string The Name of the instance */
    protected string $instancename;

    /** @var string The content of the letter */
    protected string $content;

    /** @var int Timestamp When the activity was created */
    protected int $created;

    /** @var moodle_url Link to the course */
    protected moodle_url $linktocourse;

    /** @var moodle_url The url to the activity */
    protected moodle_url $linktoactivity;

    /** @var bool variable for the mustache template */
    public bool $isbasic = true;

    /** @var string color of the letter. Only used by mustache */
    public string $lettercolor;

    // Constructor.

    /**
     * Constructor for a letter
     *
     * @param int $contentid        internal ID in the townsquare block.
     * @param int $courseid         Course ID from where the content comes from.
     * @param string $modulename    Name of the module/activity.
     * @param string $instancename  Name of the instance.
     * @param string $content        The content that will be showed in the letter.
     * @param int $created          Timestamp of creation.
     * @param int $cmid             Course module id of the content module.
     */
    public function __construct($contentid, $courseid, $modulename, $instancename, $content, $created, $cmid) {
        $this->contentid = $contentid;
        $this->lettertype = 'basic';
        $this->courseid = $courseid;
        $this->cmid = $cmid;
        $this->coursename = get_course($courseid)->fullname;
        $this->modulename = $modulename;
        $this->instancename = $instancename;
        $this->content = $content;
        $this->created = $created;
        $this->linktocourse = new moodle_url('/course/view.php', ['id' => $this->courseid]);
        $this->linktoactivity = new moodle_url('/mod/' . $modulename . '/view.php', ['id' => $cmid]);
        $this->lettercolor = townsquare_get_colorsetting('basicletter');
    }

    // Functions.

    /**
     * Export function for the mustache template.
     * @return array
     */
    public function export_letter(): array {
        // Change the timestamp to a date.
        return [
            'contentid' => $this->contentid,
            'lettertype' => $this->lettertype,
            'isbasic' => $this->isbasic,
            'courseid' => $this->courseid,
            'coursename' => $this->coursename,
            'instancename' => $this->instancename,
            'content' => $this->content,
            'created' => date('d.m.Y', $this->created),
            'createdtimestamp' => $this->created,
            'linktocourse' => $this->linktocourse->out(),
            'linktoactivity' => $this->linktoactivity->out(),
            'basiclettercolor' => $this->lettercolor,
        ];
    }
}
