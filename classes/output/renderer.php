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

use block_townsquare\contentcontroller;
use cache;
use core\exception\moodle_exception;
use plugin_renderer_base;

/**
 * Townsquare block renderer.
 *
 * @package    block_townsquare
 * @copyright  2023 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Return the main content for the block townsquare.
     *
     * @return string HTML string
     * @throws moodle_exception
     */
    public function render_main(): string {
        $controller = new contentcontroller();
        $letters = $controller->get_content();

        // Get the last update time from the cache.
        $lastupdate = cache::make('block_townsquare', 'townsquareevents')->get('allevents')["lastupdate"];
        $mustachedata = (object) [
            'content' => $letters,
            'courses' => $controller->courses,
            'lastupdate' => get_string('reload_message', 'block_townsquare', date('G:i', $lastupdate)),
            'reloadlabel' => self::get_reloadlabel($lastupdate),
            'timespans' => self::get_timespans(),
        ];
        return $this->render_from_template('block_townsquare/main', $mustachedata);
    }

    /**
     * Renders only the content (the letters). Used for reloads.
     * @return string
     */
    public function render_content(): string {
        // Get the content first to fill the cache. This should not be moved.
        $controller = new contentcontroller();
        $letters = $controller->get_content();

        // Get the last update time from the cache.
        $lastupdate = cache::make('block_townsquare', 'townsquareevents')->get('allevents')["lastupdate"];
        $mustachedata = (object) [
            'lastupdate' => get_string('reload_message', 'block_townsquare', date('G:i', $lastupdate)),
            'reloadlabel' => self::get_reloadlabel($lastupdate),
            'content' => $letters,
            // The course filter is updated with the courses of the reloaded content.
            'courses' => $controller->courses,
        ];
        return $this->render_from_template('block_townsquare/content', $mustachedata);
    }

    /**
     * Returns the time spans of the time filter. A time span covers the same time before and after now.
     *
     * @return array
     */
    private static function get_timespans(): array {
        $timespans = ['twodays' => 2 * DAYSECS, 'fivedays' => 5 * DAYSECS, 'week' => WEEKSECS, 'month' => 30 * DAYSECS];
        $result = [];
        foreach ($timespans as $name => $seconds) {
            $result[] = [
                'id' => "ts_time_{$name}",
                'timespan' => $seconds,
                'label' => get_string("timespan_{$name}", 'block_townsquare'),
            ];
        }
        return $result;
    }

    /**
     * Returns the label of the reload button, e.g. "Reload (last update: 2:30 PM)".
     * The time is shown in the timezone and time format of the user.
     *
     * @param int $lastupdate Timestamp of the last update
     * @return string
     */
    private static function get_reloadlabel(int $lastupdate): string {
        $time = userdate($lastupdate, get_string('strftimetime', 'langconfig'));
        return get_string('reloadwithtime', 'block_townsquare', $time);
    }
}
