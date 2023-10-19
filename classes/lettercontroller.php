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
 * Letter Controller of block_townsquare
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_townsquare;

use block_townsquare\letter;
use moodle_exception;
use stdClass;

/**
 * Letter Controller Class.
 * This Class controls the logic of townsquare. It retrieves all important events,
 * builds the letters and calls the renderer functions to draw the letters.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lettercontroller {

    /** @var stdClass Class to retrieve events */
    public $townsquareevents;

    /** @var array events that are relevant for the townsquare */
    public $events;

    /** @var array letters that will be shown to the user */
    public $letters;

    public function __construct() {
        $this->townsquareevents = new townsquareevents();
        $this->events = [];
        $this->letters = [];
    }

    // Core functions.

    /**
     * Uses the townsquareevents class to retrieve all important events.
     * @return array
     */
    public function retrieve_events() {
        $this->events = $this->townsquareevents->townsquare_get_all_events_sorted();
        return $this->events;
    }

    /**
     * Builds the letters for the events.
     * @return array
     * @throws moodle_exception
     */
    public function build_letters() {
        $this->retrieve_events();

        $index = 0;
        // Build a letter for each event.
        foreach ($this->events as $event) {
            if ($event->eventtype == 'post') {
                $templetter = new letter\post_letter($index, $event);
            } else if ($event->eventtype == 'expectcompletionon') {
                $templetter = new letter\activitycompletion_letter($index, $event);
            } else {
                $templetter = new letter\letter($index, $event->courseid, $event->modulename, $event->name, $event->timestart);
            }
            $this->letters[$index] = $templetter->export_letter();
            $index++;
        }

        return $this->letters;
    }

    // Getter.

    /**
     * Getter for the letters
     * @return array
     */
    public function get_letters() {
        return $this->letters;
    }

    // Helper functions.

}
