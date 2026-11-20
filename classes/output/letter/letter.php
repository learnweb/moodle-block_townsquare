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

namespace block_townsquare\output\letter;

use cm_info;
use core\output\named_templatable;
use core\output\renderable;
use core\output\renderer_base;
use core_course\output\activity_icon;
use moodle_url;

/**
 * A letter is one row in Town Square. It prepares an item for its template.
 *
 * This class contains what all letters have: the activity with its course, the time and the activity icon.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class letter implements named_templatable, renderable {
    /** @var int an ID to identify every letter in townsquare */
    protected int $contentid;

    /** @var cm_info The activity of the letter */
    protected cm_info $cm;

    /** @var int Timestamp of the letter */
    protected int $time;

    /**
     * Constructor for a letter.
     *
     * @param int $contentid internal ID in the townsquare block
     * @param int $courseid Id of the course of the activity
     * @param int $cmid Course module id of the activity
     * @param int $time Timestamp of the letter
     */
    public function __construct(int $contentid, int $courseid, int $cmid, int $time) {
        $this->contentid = $contentid;
        $this->cm = get_fast_modinfo($courseid)->get_cm($cmid);
        $this->time = $time;
    }

    /**
     * Returns the timestamp of the letter.
     * @return int
     */
    public function get_time(): int {
        return $this->time;
    }

    /**
     * Returns the activity of the letter.
     * @return cm_info
     */
    public function get_cm(): cm_info {
        return $this->cm;
    }

    /**
     * Exports what all letters have.
     * @param renderer_base $output
     * @return array
     */
    protected function export_basics(renderer_base $output): array {
        return [
            'contentid' => $this->contentid,
            'courseid' => $this->cm->course,
            'coursename' => $this->cm->get_course()->shortname,
            'instancename' => $this->cm->name,
            'created' => userdate($this->time, get_string('strftimetime', 'langconfig')),
            'createdtimestamp' => $this->time,
            'linktocourse' => (new moodle_url('/course/view.php', ['id' => $this->cm->course]))->out(),
            'linktoactivity' => $this->get_activityurl($this->cm->id),
            'activityicon' => $this->get_activityicon($output),
        ];
    }

    /**
     * Returns the url to an activity of the module of the letter.
     * @param int $cmid Course module id of the activity
     * @return string
     */
    protected function get_activityurl(int $cmid): string {
        return (new moodle_url("/mod/{$this->cm->modname}/view.php", ['id' => $cmid]))->out();
    }

    /**
     * Returns the activity icon of the module for the template core_course/activity_icon.
     * @param renderer_base $output
     * @return array
     */
    private function get_activityicon(renderer_base $output): array {
        return activity_icon::from_modname($this->cm->modname)->set_extra_classes('smaller')->export_for_template($output);
    }
}
