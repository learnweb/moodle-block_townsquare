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
 * Internal library of functions for the townsquare block
 *
 * @package block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Gets the id of all courses where the current user is enrolled
 * @return array
 */
function townsquare_get_courses(): array {
    global $USER;

    $enrolledcourses = enrol_get_all_users_courses($USER->id, true);
    $courses = [];
    foreach ($enrolledcourses as $enrolledcourse) {
        $courses[] = $enrolledcourse->id;
    }

    return $courses;
}

/**
 * Merge sort function for townsquare events.
 * @param $events
 * @return array
 */
function townsquare_mergesort($events): array {
    $length = count($events);
    if ($length <= 1) {
        return $events;
    }
    $mid = (int) ($length / 2);
    $left = townsquare_mergesort(array_slice($events, 0, $mid));
    $right = townsquare_mergesort(array_slice($events, $mid));
    return townsquare_merge($left, $right);
}

/**
 * Function that sorts events in descending order by time created (newest event first)
 * @param array $left
 * @param array $right
 * @return array
 */
function townsquare_merge(array $left, array $right): array {
    $result = [];
    reset($left);
    reset($right);
    $numberofelements = count($left) + count($right);
    for ($i = 0; $i < $numberofelements; $i++) {
        if (current($left) && current($right)) {
            if (current($left)->timestart > current($right)->timestart) {
                $result[$i] = current($left);
                next($left);
            } else {
                $result[$i] = current($right);
                next($right);
            }
        } else if (current($left)) {
            $result[$i] = current($left);
            next($left);
        } else {
            $result[$i] = current($right);
            next($right);
        }
    }
    return $result;
}
