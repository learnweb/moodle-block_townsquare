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

/**
 * Letter Controller Class.
 *
 * This Class controls the logic of townsquare. It retrieves all important events and builds the letters.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contentcontroller {

    /** @var object Class to retrieve events */
    public $townsquareevents;

    /** @var array events that are relevant for the townsquare */
    public $events;

    /** @var array letters and other content that will be shown to the user */
    public $content;

    /**
     * Constructor for the controller.
     */
    public function __construct() {
        $this->townsquareevents = new townsquareevents();
        $this->events = [];
        $this->content = [];
    }

    // Core functions.

    /**
     * Builds the content from events.
     * @return array
     */
    public function build_content():array {
        $this->events = $this->townsquareevents->townsquare_get_all_events_sorted();

        $orientationmarkerset = false;
        $index = 0;
        $time = time();
        // Build a letter for each event.
        foreach ($this->events as $event) {
            // Display a orientation marker on the current date between the other events.
            if (!$orientationmarkerset && (
               ($event->eventtype != 'post' && $event->timestart <= $time) ||
               ($event->eventtype == 'post' && $event->postcreated <= $time))) {

                $orientationmarkerset = true;
                $tempcontent = new orientation_marker($index, $time);
                $this->content[$index] = $tempcontent->export_data();
                $index++;
            }
            if ($event->eventtype == 'post') {
                $templetter = new letter\post_letter($index, $event);
            } else if ($event->eventtype == 'expectcompletionon') {
                $templetter = new letter\activitycompletion_letter($index, $event);
            } else {
                $templetter = new letter\letter($index, $event->courseid, $event->modulename, $event->name, $event->timestart);
            }
            $this->content[$index] = $templetter->export_letter();
            $index++;
        }
        return $this->content;
    }

    // Getter.

    /**
     * Getter for the content
     * @return array
     */
    public function get_content():array {
        return $this->content;
    }

    /**
     * Getter for the events
     * @return array
     */
    public function get_events():array {
        return $this->events;
    }
}
