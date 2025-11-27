<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Event observers used in Townsquare.
 *
 * @package   block_townsquare
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_townsquare;

use core\event\course_deleted;

/**
 * This class listens to certain events triggered in Moodle and apply them to townsquare.
 *
 * @package   block_townsquare
 * @copyright 2025 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Handle course deleted event.
     *
     * @param course_deleted $event The event object.
     */
    public static function course_deleted(course_deleted $event) {
        global $DB;
        $records = $DB->get_records('block_townsquare_preferences');

        // Go trough each record and check if the deleted course appears.
        foreach ($records as $record) {
            $json = json_decode($record->courses);
            if (property_exists($json, $event->courseid)) {
                unset($json->{$event->courseid});
                $record->courses = json_encode($json);
                $DB->update_record('block_townsquare_preferences', $record);
            }
        }
    }
}
