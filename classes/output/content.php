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

namespace block_townsquare\output;

use block_townsquare\item\post_item;
use block_townsquare\output\letter\deadline_letter;
use block_townsquare\output\letter\letter;
use block_townsquare\output\letter\post_letter;
use block_townsquare\townsquareevents;
use cache;
use core\output\named_templatable;
use core\output\renderable;
use core\output\renderer_base;

/**
 * The content of Town Square: the letters grouped by day. It is also rendered alone after a reload.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content implements named_templatable, renderable {
    #[\Override]
    public function get_template_name(renderer_base $renderer): string {
        return 'block_townsquare/content';
    }

    #[\Override]
    public function export_for_template(renderer_base $output): array {
        $letters = $this->get_letters();

        // The courses that show letters, for the course filter.
        $courses = [];
        foreach ($letters as $letter) {
            $course = $letter->get_cm()->get_course();
            $courses[$course->id] ??= ['courseid' => $course->id, 'coursename' => $course->shortname];
        }

        $lettergroups = array_map(
            fn($lettergroup) => ['lettergroup' => $output->render($lettergroup)],
            $this->group_by_day($letters)
        );

        // The reload button shows the time of the last update. The letters were built before, so the cache is filled.
        $lastupdate = cache::make('block_townsquare', 'townsquareevents')->get('items')['lastupdate'];
        $lastupdate = userdate($lastupdate, get_string('strftimetime', 'langconfig'));
        return [
            'lettergroups' => $lettergroups,
            'courses' => array_values($courses),
            'reloadlabel' => get_string('reloadwithtime', 'block_townsquare', $lastupdate),
        ];
    }

    /**
     * Builds a letter for each post and each group of deadlines.
     *
     * @return letter[] The letters, the latest first
     */
    public function get_letters(): array {
        $letters = [];
        foreach ($this->group_deadlines((new townsquareevents())->get_items()) as $index => $entry) {
            $letters[] = $entry instanceof post_item ? new post_letter($index, $entry) : new deadline_letter($index, $entry);
        }
        return $letters;
    }

    /**
     * Groups the letters by their day in the timezone of the user. The letters closest to now come first: today and
     * the upcoming days with the earliest letter first, then the past days with the latest letter first. The first past
     * group gets the heading "Past".
     *
     * @param letter[] $letters The letters, the latest first
     * @return lettergroup[]
     */
    public function group_by_day(array $letters): array {
        $days = [];
        foreach ($letters as $letter) {
            $days[usergetmidnight($letter->get_time())][] = $letter;
        }

        $today = usergetmidnight(time());
        $upcoming = [];
        $past = [];
        foreach ($days as $daystart => $dayletters) {
            if ($daystart < $today) {
                $past[] = new lettergroup($daystart, $dayletters, firstpast: !$past);
            } else {
                $upcoming[] = new lettergroup($daystart, array_reverse($dayletters));
            }
        }
        return array_merge(array_reverse($upcoming), $past);
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
}
