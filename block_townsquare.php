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

/**
 * Townsquare block class.
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
     * @return stdClass The block HTML.
     * @throws dml_exception
     * @throws \core\exception\moodle_exception
     */
    public function get_content(): stdClass {
        global $DB, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = $this->page->get_renderer('block_townsquare')->render_main();

        // Get the user settings if available.
        $usersettings = $DB->get_record('block_townsquare_preferences', ['userid' => $USER->id]);

        // Load all javascripts.
        $this->page->requires->js_call_amd('block_townsquare/postletter', 'init');
        $this->page->requires->js_call_amd('block_townsquare/coursefilter', 'init');
        $this->page->requires->js_call_amd('block_townsquare/timefilter', 'init');
        $this->page->requires->js_call_amd('block_townsquare/letterfilter', 'init');
        $this->page->requires->js_call_amd('block_townsquare/filtercontroller', 'init');
        $this->page->requires->js_call_amd('block_townsquare/lettergroup', 'init');
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
    public function has_config(): bool {
        return true;
    }
}
