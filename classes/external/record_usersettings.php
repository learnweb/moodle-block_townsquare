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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\restricted_context_exception;
use dml_exception;
use invalid_parameter_exception;
use context_user;

/**
 * Class implementing the external API, esp. for AJAX functions.
 * Saves usersettings in the database.
 *
 * @package    block_townsquare
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class record_usersettings extends external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'timefilterpast' => new external_value(PARAM_INT, 'time span for filtering the past'),
                'timefilterfuture' => new external_value(PARAM_INT, 'time span for filtering the future'),
                'basicletter' => new external_value(PARAM_INT, 'Setting of the letter filter for basic letters'),
                'completionletter' => new external_value(PARAM_INT, 'Setting of the letter filter for completion letters'),
                'postletter' => new external_value(PARAM_INT, 'Setting of the letter filter for post letters'),
                'courses' => new external_value(PARAM_TEXT, 'courses the user wants to see letters from'),
            ]
        );
    }

    /**
     * Return the result of the record_usersettings function
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_BOOL, 'true if successful');
    }

    /**
     * Record the user settings
     *
     * @param int $timefilterpast Time span for filtering the past
     * @param int $timefilterfuture Time span for filtering the future
     * @param int $basicletter If basic letters should be shown
     * @param int $completionletter If completion letters should be shown
     * @param int $postletter If post letters should be shown
     * @param string $courses The setting for the course filter
     * @return bool
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     * @throws dml_exception
     */
    public static function execute(
        int $timefilterpast,
        int $timefilterfuture,
        int $basicletter,
        int $completionletter,
        int $postletter,
        string $courses
    ): bool {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(), [
            'timefilterpast' => $timefilterpast, 'timefilterfuture' => $timefilterfuture,
            'basicletter' => $basicletter, 'completionletter' => $completionletter, 'postletter' => $postletter,
            'courses' => $courses,
        ]);
        self::validate_context(context_user::instance($USER->id));

        $record = (object) $params;
        $record->userid = $USER->id;
        // Update the existing record or create a new one.
        if ($id = $DB->get_field('block_townsquare_preferences', 'id', ['userid' => $USER->id])) {
            $record->id = $id;
            $DB->update_record('block_townsquare_preferences', $record);
        } else {
            $DB->insert_record('block_townsquare_preferences', $record);
        }
        return true;
    }
}
