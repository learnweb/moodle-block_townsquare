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
 * Function to get the color of a letter.
 *
 * @param string $lettertype        The type of the letter that wants to retrieve its color setting.
 * @return false|string              The color of the letter.
 * @throws moodle_exception
 */
function townsquare_get_colorsetting($lettertype): string {
    switch ($lettertype) {
        case 'basicletter':
            return get_config('block_townsquare', 'basiclettercolor');
        case 'postletter':
            return get_config('block_townsquare', 'postlettercolor');
        case 'completionletter':
            return get_config('block_townsquare', 'completionlettercolor');
        case 'orientationmarker':
            return get_config('block_townsquare', 'orientationmarkercolor');
        default:
            throw new moodle_exception('invalidlettertype', 'block_townsquare');
    }
}

/**
 * General Support function for core events.
 * Can be used to modify the event content, as in some cases, core events don't have a good text in the events-datatable.
 * @param object $event  The event, that is being checked.
 * @return void
 */
function townsquare_check_coreevent(&$event): void {
    // Activity completion event have a own message handling (as it always has the same structure).
    if ($event->eventtype == 'expectcompletionon') {
        return;
    }

    $time = date('H:i', $event->timestart);

    // Most modules only have open and closing events.
    $opencloseevents = ['choice' , 'data', 'feedback', 'lesson', 'quiz', 'scorm'];
    if (in_array($event->modulename, $opencloseevents)) {
        $event->name = townsquare_get_open_close_message($event, $time);
    }

    // Other core plugins have extra/other event types.
    if ($event->modulename == 'assign') {
        // Event type is either 'due' or 'gradingdue'.
        $identifier = 'assign' . $event->eventtype . 'message';
        $event->name = get_string($identifier, 'block_townsquare', ['time' => $time]);
    } else if ($event->modulename == 'chat' && $event->eventtype == 'chattime') {
        $event->name = get_string('chattimemessage', 'block_townsquare', ['time' => $time]);
    } else if ($event->modulename == 'forum') {
        if ($event->eventtype == 'due') {
            $event->name = get_string('forumduemessage', 'block_townsquare', ['time' => $time]);
        }
    } else if ($event->modulename == 'workshop') {
        // Event type is either 'opensubmission', 'closesubmission', 'openassessment' or 'closeassessment'.
        $identifier = 'workshop' . $event->eventtype;
        $event->name = get_string($identifier, 'block_townsquare', ['time' => $time]);
    }
}

/**
 * Helper function for the check function. Helps to reduce repetitive checks
 * @param $event
 * @param $pluginname
 * @param $time
 * @return string
 */
function townsquare_get_open_close_message($event, $time) {
    if ($event->eventtype == 'open') {
        return get_string($event->modulename . 'openmessage', 'block_townsquare', ['time' => $time]);
    } else if ($event->eventtype == 'close') {
        return get_string($event->modulename . 'closemessage', 'block_townsquare', ['time' => $time]);
    }
    return '';
}
