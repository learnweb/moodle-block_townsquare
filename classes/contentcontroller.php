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

use block_townsquare\item\post_item;

/**
 * Letter Controller of block_townsquare
 *
 * This Class controls access to the townsquareevents class. It retrieves all items and builds the letters.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contentcontroller {
    /** @var townsquareevents Class to retrieve the items */
    public townsquareevents $townsquareevents;

    /** @var array items (deadlines and posts) that are relevant for the townsquare, the latest first */
    public array $items;

    /** @var array stores the letters in objects with the day the letters are from.
     * The content is structured in a way that mustache can parse it easily:
     *
     * ['Y-m-d'] => {
     *     string $day: 'Y-m-d;
     *     array $letters: key => $letterobject
     * }
     * At the end, the array is normalized with array_values so mustache can iterate over it.
     */
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
     * Builds the content from the items.
     * @return array
     */
    public function build_content(): array {
        $this->content = [];
        $this->items = $this->townsquareevents->get_items();

        $index = 0;
        $appearedcourses = [];

        // Build a letter for each post and each group of deadlines.
        foreach ($this->group_deadlines($this->items) as $entry) {
            $templetter = $entry instanceof post_item
                ? new local\letter\post_letter($index++, $entry)
                : new local\letter\deadline_letter($index++, $entry);
            $templetter = $templetter->export_letter();

            // Group the letters by its day in the timezone of the user.
            $day = $this->get_day($templetter['createdtimestamp']);
            if (!isset($this->content[$day])) {
                $this->content[$day] = (object) [
                    'day' => $day,
                    'daystart' => usergetmidnight($templetter['createdtimestamp']),
                    'daylabel' => $this->get_daylabel($day),
                    'daydate' => userdate($templetter['createdtimestamp'], get_string('strftimedayshort', 'langconfig')),
                    'count' => 0,
                    'letters' => [],
                ];
            }
            $this->content[$day]->letters[] = $templetter;
            $this->content[$day]->count++;

            // Collect the courses shown in the townsquare to be able to filter them afterwards.
            if (!array_key_exists($templetter['courseid'], $appearedcourses)) {
                $this->courses[] = [
                    'courseid' => $templetter['courseid'],
                    'coursename' => $templetter['coursename'],
                ];
                $appearedcourses[$templetter['courseid']] = true;
            }
        }
        return $this->sort_by_distance_to_now(array_values($this->content));
    }

    /**
     * Groups the deadlines of the same course and module at the same time, so they are shown in one letter. This is
     * often the case when exercises are split up (like: week 1 - exercise 1, week 1 - exercise 2, ...).
     *
     * @param array $items deadline_item and post_item objects, the latest first
     * @return array post_item objects and arrays of deadline_item objects, in the order of the items
     */
    private function group_deadlines(array $items): array {
        $entries = [];
        $groups = [];
        foreach ($items as $item) {
            if ($item instanceof post_item) {
                $entries[] = $item;
                continue;
            }
            $modulename = get_fast_modinfo($item->courseid)->get_cm($item->cmid)->modname;
            $key = "{$item->courseid}-{$modulename}-{$item->time}";
            if (!isset($groups[$key])) {
                $groups[$key] = count($entries);
                $entries[] = [];
            }
            $entries[$groups[$key]][] = $item;
        }
        return $entries;
    }

    /**
     * Sorts the days, so that the letters closest to now come first: today and the upcoming days with the earliest
     * letter first, then the past days with the latest letter first. The first past day gets the heading "Past".
     *
     * @param array $days Days with their letters, from the latest to the earliest letter (order of the items)
     * @return array
     */
    private function sort_by_distance_to_now(array $days): array {
        $today = usergetmidnight(time());
        $upcoming = [];
        $past = [];
        foreach ($days as $day) {
            $day->ispast = $day->daystart < $today;
            if ($day->ispast) {
                $past[] = $day;
            } else {
                $day->letters = array_reverse($day->letters);
                $upcoming[] = $day;
            }
        }
        if ($past) {
            $past[0]->firstpast = true;
        }
        return array_merge(array_reverse($upcoming), $past);
    }

    /**
     * Returns the day of a timestamp in the timezone of the user, e.g. "16.11.2026". Is used to group the letters.
     *
     * @param int $timestamp
     * @return string
     */
    private function get_day(int $timestamp): string {
        return userdate($timestamp, '%d.%m.%Y', 99, false);
    }

    /**
     * Returns "Today", "Tomorrow" or "Yesterday" for a day, otherwise an empty string.
     *
     * @param string $day Day in the format of get_day()
     * @return string
     */
    private function get_daylabel(string $day): string {
        // A day has 23 to 25 hours (daylight saving time), so the days next to today are found from its midnight.
        $today = usergetmidnight(time());
        return match ($day) {
            $this->get_day($today) => get_string('today', 'calendar'),
            $this->get_day($today + DAYSECS + 2 * HOURSECS) => get_string('tomorrow', 'calendar'),
            $this->get_day($today - 2 * HOURSECS) => get_string('yesterday', 'calendar'),
            default => '',
        };
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
     * Getter for the items
     * @return array
     */
    public function get_items(): array {
        return $this->items;
    }
}
