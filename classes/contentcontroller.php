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
 * This Class controls access to the townsquareevents class. It retrieves all important events and builds the letters.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contentcontroller {
    /** @var object Class to retrieve events */
    public object $townsquareevents;

    /** @var array events that are relevant for the townsquare */
    public array $events;

    /** @var array letters and other content that will be shown to the user */
    public array $content;

    /** @var array courses that show content in townsquare (not the same as enrolled courses) */
    public array $courses;

    /**
     * Constructor for the controller.
     */
    public function __construct() {
        $this->townsquareevents = new townsquareevents();
        $this->courses = [];
        $this->content = [];
    }

    // Core functions.

    /**
     * Builds the content from events.
     * @return array
     */
    public function build_content(): array {
        $this->content = [];
        $this->events = $this->townsquareevents->get_all_events_sorted();

        $index = 0;
        $appearedcourses = [];
        // Build a letter for each event.
        foreach ($this->events as $event) {
            match ($event->eventtype) {
                'post' => $templetter = new local\letter\post_letter($index, $event),
                'expectcompletionon' => $templetter = new local\letter\activitycompletion_letter($index, $event),
                default => $templetter = new local\letter\letter(
                    $index,
                    $event->courseid,
                    $event->modulename,
                    $event->instancename,
                    $event->content,
                    $event->timestart,
                    $event->coursemoduleid
                ),
            };
            $templetter = $templetter->export_letter();
            $this->content[$index] = $templetter;

            // Collect the courses shown in the townsquare to be able to filter them afterwards.
            if (!array_key_exists($this->content[$index]['courseid'], $appearedcourses)) {
                $this->courses[] = ['courseid' => $this->content[$index]['courseid'],
                                    'coursename' => $this->content[$index]['coursename'], ];
                $appearedcourses[$event->courseid] = true;
            }
            $index++;
        }
        return $this->content;
    }

    // Getter.

    /**
     * Getter for the content
     * @return array
     */
    public function get_content(): array {
        return $this->build_content();
    }

    /**
     * Getter for the events
     * @return array
     */
    public function get_events(): array {
        return $this->events;
    }
}
