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
 * Class that represents an activity completion reminder.
 *
 * Subclass from letter.
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activitycompletion_letter extends letter {

    // Attributes.

    /** @var bool variable for the mustache template */
    public bool $isactivitycompletion = true;

    // Constructor.

    /**
     * Constructor for the activity completion letter
     *
     * @param int $contentid        internal ID in the townsquare block
     * @param object $calendarevent a calendar event with information, for more see classes/townsquareevents.php
     */
    public function __construct($contentid, $calendarevent) {
        parent::__construct($contentid, $calendarevent->courseid, $calendarevent->modulename, $calendarevent->instancename,
                                        $calendarevent->name, $calendarevent->timestart, $calendarevent->coursemoduleid);
        $this->lettertype = 'activitycompletion';
    }

    // Functions.

    /**
     * Export function for the mustache template.
     * return array
     */
    public function export_letter():array {
        // Change the timestamp to a date.
        $date = date('d.m.Y', $this->created);

        return [
            'contentid' => $this->contentid,
            'lettertype' => $this->lettertype,
            'isactivitycompletion' => $this->isactivitycompletion,
            'courseid' => $this->courseid,
            'coursename' => $this->coursename,
            'instancename' => $this->instancename,
            'content' => $this->content,
            'created' => $date,
            'linktoactivity' => $this->linktoactivity->out(),
            'linktocourse' => $this->linktocourse->out(),
        ];
    }

}
