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

namespace block_townsquare\external;

use cache;
use core\exception\coding_exception;
use external_function_parameters;
use external_api;
use external_value;
use block_townsquare\townsquareevents;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/externallib.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Class implementing the external API, esp. for AJAX functions.
 * Rebuilds the townsquare cache,
 *
 * @package    block_townsquare
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reload extends external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Return the result of the reload function
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_RAW, 'The new block content html');
    }

    /**
     * Rebuilds the cache.
     *
     * @return int
     * @throws coding_exception|\moodle_exception
     */
    public static function execute(): string {
        global $PAGE, $USER, $CFG;
        require_once($CFG->dirroot . '/lib/filelib.php');
        // Set the context for the page.
        $PAGE->set_context(\context_user::instance($USER->id));
        cache::make('block_townsquare', 'townsquareevents')->delete('allevents');
        return $PAGE->get_renderer('block_townsquare')->render_content();
    }
}
