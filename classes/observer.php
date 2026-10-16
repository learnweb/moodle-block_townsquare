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

namespace block_townsquare;

use core\event\course_deleted;
use dml_exception;

/**
 * Event observers used in Townsquare.
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
     * @return void
     * @throws dml_exception
     */
    public static function course_deleted(course_deleted $event): void {
        global $DB;
        // Get the course id that is getting deleted.
        $courseid = (string) $event->courseid;

        // Only load records that may contain the course id (JSON key "<courseid>":).
        $records = $DB->get_records_select(
            table: 'block_townsquare_preferences',
            select: $DB->sql_like('courses', ':pattern'),
            params: ['pattern' => '%"' . $courseid . '":%'],
            fields: 'id, courses'
        );

        // Go through each record that may have the course and delete it from the preferences.
        foreach ($records as $record) {
            $json = json_decode($record->courses ?? '');
            if (is_object($json) && property_exists($json, $courseid)) {
                unset($json->{$courseid});
                $record->courses = json_encode($json);
                $DB->update_record('block_townsquare_preferences', $record);
            }
        }
    }
}
