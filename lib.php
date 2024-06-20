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
 * Function for subplugins to get the start time of the search.
 * @return int
 */
function townsquare_get_timestart(): int {
    return time() - 15768000;
}

/**
 * Function for subplugins to get the end time of the search.
 * @return int
 */
function townsquare_get_timeend(): int {
    return time() + 15768000;
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

// Filter functions.

function townsquare_filter_availability($event): bool {
    // If there is no restriction defined, the event is available.
    if ($event->availability == null) {
        return false;
    }

    // If there is a restriction, check if it applies to the user.
    $modinfo = get_fast_modinfo($event->courseid);
    $moduleinfo = $modinfo->get_cm($event->coursemoduleid);
    if ($moduleinfo->uservisible) {
        return false;
    }

    return true;
}

/**
 * Filter that checks if the event needs to be filtered out for the current user because it is already completed..
 * Applies to activity completion events.
 * @param object $coreevent coreevent that is checked
 * @return bool true if the event needs to filtered out, false if not.
 */
function townsquare_filter_activitycompletions($coreevent): bool {
    global $DB, $USER;
    if ($completionstatus = $DB->get_record('course_modules_completion',
        ['coursemoduleid' => $coreevent->coursemoduleid, 'userid' => $USER->id])) {
        if ($completionstatus->completionstate != 0) {
            return true;
        }
    }
    return false;
}



// Strings adaptation functions.

/**
 * General Support function for core events.
 * Can be used to modify the event content, as in some cases, core events don't have a good text in the events-datatable.
 * @param object $event  The event, that is being checked.
 * @return void
 */
function townsquare_check_coreevent($event) {
    $time = date('H:i', $event->timestart);
    if ($event->modulename == 'assign') {
        if ($event->eventtype == 'due') {
            $event->name = get_string('assignduemessage', 'block_townsquare', ['time' => $time]);
        } else if ($event->eventtype == 'gradingdue') {
            $event->name = get_string('assigngradingduemessage', 'block_townsquare', ['time' => $time]);
        }
    } else if ($event->modulename == 'chat' && $event->eventtype == 'chattime') {
        $event->name = get_string('chattimemessage', 'block_townsquare', ['time' => $time]);
    } else if ($event->modulename == 'choice') {
        if ($event->eventtype == 'open') {
            $event->name = get_string('choiceopenmessage', 'block_townsquare', ['time' => $time]);
        } else if ($event->eventtype == 'close') {
            $event->name = get_string('choiceclosemessage', 'block_townsquare', ['time' => $time]);
        }
    } else if ($event->modulename == 'data') {
        if ($event->eventtype == 'open') {
            $event->name = get_string('dataopenmessage', 'block_townsquare');
        } else if ($event->eventtype == 'close') {
            $event->name = get_string('dataclosemessage', 'block_townsquare', ['time' => $time]);
        }
    } else if ($event->modulename == 'feedback') {
        if ($event->eventtype == 'open') {
            $event->name = get_string('feedbackopenmessage', 'block_townsquare', ['time' => $time]);
        } else if ($event->eventtype == 'close') {
            $event->name = get_string('feedbackclosemessage', 'block_townsquare', ['time' => $time]);
        }
    } else if ($event->modulename == 'forum') {
        if ($event->eventtype == 'due') {
            $event->name = get_string('forumduemessage', 'block_townsquare', ['time' => $time]);
        }
    } else if ($event->modulename == 'lesson') {
        if ($event->eventtype == 'open') {
            $event->name = get_string('lessonopenmessage', 'block_townsquare', ['time' => $time]);
        } else if ($event->eventtype == 'close') {
            $event->name = get_string('lessonclosemessage', 'block_townsquare', ['time' => $time]);
        }
    } else if ($event->modulename == 'quiz') {
        if ($event->eventtype == 'open') {
            $event->name = get_string('quizopenmessage', 'block_townsquare', ['time' => $time]);
        } else if ($event->eventtype == 'close') {
            $event->name = get_string('quizclosemessage', 'block_townsquare', ['time' => $time]);
        }
    } else if ($event->modulename == 'scorm') {
        if ($event->eventtype == 'open') {
            $event->name = get_string('scormopenmessage', 'block_townsquare');
        } else if ($event->eventtype == 'close') {
            $event->name = get_string('scormclosemessage', 'block_townsquare');
        }
    } else if ($event->modulename == 'workshop') {
        if ($event->eventtype == 'opensubmission') {
            $event->name = get_string('workshopopensubmission', 'block_townsquare', ['time' => $time]);
        } else if ($event->eventtype == 'closesubmission') {
            $event->name = get_string('workshopclosesubmission', 'block_townsquare', ['time' => $time]);
        } else if ($event->eventtype == 'openassessment') {
            $event->name = get_string('workshopopenassessment', 'block_townsquare', ['time' => $time]);
        } else if ($event->eventtype == 'closeassessment') {
            $event->name = get_string('workshopcloseassessment', 'block_townsquare', ['time' => $time]);
        }
    }
}
