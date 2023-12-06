<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

use block_townsquare\contentcontroller;

/**
 * Plugin strings are defined here.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_townsquare extends block_base {

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init():void {
        $this->title = get_string('pluginname', 'block_townsquare');
    }

    /**
     * Gets the block contents.
     *
     * @return object|null The block HTML.
     */
    public function get_content():object {
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $controller = new contentcontroller();
        $mustachedata = new stdClass();
        $mustachedata->content = $controller->get_content();

        $this->content = new stdClass();
        $this->content->text = $OUTPUT->render_from_template('block_townsquare/blockcontent', $mustachedata);
        $this->page->requires->js_call_amd('block_townsquare/postletter', 'init');
        return $this->content;
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats():array {
        return [
            'admin' => false,
            'site-index' => false,
            'course-view' => false,
            'mod' => false,
            'my' => true,
        ];
    }
}
