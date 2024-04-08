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
 * @return false|mixed              The color of the letter.
 * @throws moodle_exception
 */
function townsquare_get_colorsetting($lettertype) {
    return match ($lettertype) {
        'basicletter' => get_config('block_townsquare', 'basiclettercolor'),
        'postletter' => get_config('block_townsquare', 'postlettercolor'),
        'completionletter' => get_config('block_townsquare', 'completionlettercolor'),
        'orientationmarker' => get_config('block_townsquare', 'orientationmarkercolor'),
        default => throw new \moodle_exception('invalidlettertype', 'block_townsquare'),
    };
}

/**
 * General Support function for core events.
 * Can be used to modify the event content, as in some cases, core events don't have a good text in the events-datatable.
 * @param object $event  The event, that is being checked.
 * @return void
 */
function townsquare_check_coreevent($event): void {
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
