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
    public function init(): void {
        $this->title = get_string('pluginname', 'block_townsquare');
    }

    /**
     * Gets the block contents.
     *
     * @return object|null The block HTML.
     */
    public function get_content(): object {
        global $OUTPUT, $DB, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $controller = new contentcontroller();
        $mustachedata = new stdClass();
        $mustachedata->content = $controller->get_content();
        $mustachedata->courses = $controller->courses;
        $mustachedata->savehelpicon = ['text' => get_string('savehelpicontext', 'block_townsquare')];
        $mustachedata->resethelpicon = ['text' => get_string('resethelpicontext', 'block_townsquare')];
        $this->content = new stdClass();
        $this->content->text = $OUTPUT->render_from_template('block_townsquare/blockcontent', $mustachedata);

        // Get the user settings if available.
        $usersettings = $DB->get_record('block_townsquare_preferences', ['userid' => $USER->id]);

        // Load all javascripts.
        $this->page->requires->js_call_amd('block_townsquare/postletter', 'init');
        $this->page->requires->js_call_amd('block_townsquare/coursefilter', 'init');
        $this->page->requires->js_call_amd('block_townsquare/timefilter', 'init');
        $this->page->requires->js_call_amd('block_townsquare/letterfilter', 'init');
        $this->page->requires->js_call_amd('block_townsquare/filtercontroller', 'init');
        $this->page->requires->js_call_amd('block_townsquare/usersettings_save', 'init', [$USER->id, $usersettings]);
        $this->page->requires->js_call_amd('block_townsquare/usersettings_reset', 'init', [$USER->id]);
        return $this->content;
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats(): array {
        return [
            'admin' => false,
            'site-index' => false,
            'course-view' => false,
            'mod' => false,
            'my' => true,
        ];
    }

    /**
     * Returns true if this block has global config.
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }
}
