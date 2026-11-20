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
 * Library of functions of the townsquare block, that can be used moodle wide.
 *
 * @package block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Time constants.
define('BLOCK_TOWNSQUARE_TIME_TWOMONTHS', 5259486); // 60.87 days.
define('BLOCK_TOWNSQUARE_TIME_THREEMONTHS', 7889229); // 91.31 days.
define('BLOCK_TOWNSQUARE_TIME_SIXMONTHS', 15778463); // 182,62 days.
define('BLOCK_TOWNSQUARE_CACHE_TTL', 1800); // 30 minutes.

/**
 * Gets the id of all courses where the current user is enrolled
 * @return array
 * @throws coding_exception
 */
function block_townsquare_get_courses(): array {
    global $USER;

    $enrolledcourses = enrol_get_all_users_courses($USER->id, true);
    $courses = [];
    foreach ($enrolledcourses as $enrolledcourse) {
        $courses[] = $enrolledcourse->id;
    }

    return $courses;
}

/**
 * Function for subplugins to get the start time of the search.
 * @return int
 * @throws dml_exception
 */
function block_townsquare_get_timestart(): int {
    return time() - get_config('block_townsquare', 'timespan');
}

/**
 * Function for subplugins to get the end time of the search.
 * @return int
 * @throws dml_exception
 */
function block_townsquare_get_timeend(): int {
    return time() + get_config('block_townsquare', 'timespan');
}

// Filter functions.

/**
 * Filter that checks if the event needs to be filtered out for the current user because it is already completed.
 * Applies to activity completion events.
 * @param object $coreevent coreevent that is checked
 * @return bool true if the event needs to filtered out, false if not.
 */
function townsquare_filter_activitycompletions($coreevent): bool {
    global $DB, $USER;
    if (
        $completionstatus = $DB->get_record(
            'course_modules_completion',
            ['coursemoduleid' => $coreevent->coursemoduleid, 'userid' => $USER->id]
        )
    ) {
        if ($completionstatus->completionstate != 0) {
            return true;
        }
    }
    return false;
}
