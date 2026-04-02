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
        global $CFG;
        $controller = new contentcontroller();
        $letters = $controller->get_content();

        // Get the last update time from the cache.
        $lastupdate = cache::make('block_townsquare', 'townsquareevents')->get('allevents')["lastupdate"];
        $mustachedata = (object) [
            'content' => $letters,
            'courses' => $controller->courses,
            'savehelpicon' => ['text' => get_string('savehelpicontext', 'block_townsquare')],
            'resethelpicon' => ['text' => get_string('resethelpicontext', 'block_townsquare')],
            'newsidepanel' => $CFG->branch >= 500,
            'lastupdate' => get_string('reload_message', 'block_townsquare', date('G:i:s', $lastupdate)),
        ];
        return $this->render_from_template('block_townsquare/main', $mustachedata);
    }

    /**
     * Renders only the content (the letters). Used for reloads.
     * @return string
     */
    public function render_content(): string {
        // Get the content first to fill the cache. This should not be moved.
        $letters = (new contentcontroller())->get_content();

        // Get the last update time from the cache.
        $lastupdate = cache::make('block_townsquare', 'townsquareevents')->get('allevents')["lastupdate"];
        $mustachedata = (object) [
            'lastupdate' => get_string('reload_message', 'block_townsquare', date('G:i:s', $lastupdate)),
            'content' => $letters,
        ];
        return $this->render_from_template('block_townsquare/content', $mustachedata);
    }
}
