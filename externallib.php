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
 * External townsquare API
 *
 * @package    block_townsquare
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * Class implementing the external API, esp. for AJAX functions.
 *
 * @package    block_townsquare
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_townsquare_external extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function record_usersettings_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'the user id'),
                'timefilterpast' => new external_value(PARAM_INT, 'time span for filtering the past'),
                'timefilterfuture' => new external_value(PARAM_INT, 'time span for filtering the future'),
                'basicletter' => new external_value(PARAM_INT, 'Setting of the letter filter for basic letters'),
                'completionletter' => new external_value(PARAM_INT, 'Setting of the letter filter for completion letters'),
                'postletter' => new external_value(PARAM_INT, 'Setting of the letter filter for post letters'),
            ]
        );
    }

    /**
     * Return the result of the record_usersettings function
     * @return external_value
     */
    public static function record_usersettings_returns() {
        return new external_value(PARAM_BOOL, 'true if successful');
    }

    /**
     * Record the user settings
     *
     * @param int $userid               The user id
     * @param int $timefilterpast       Time span for filtering the past
     * @param int $timefilterfuture     Time span for filtering the future
     * @param int $basicletter          If basic letters should be shown
     * @param int $completionletter     If completion letters should be shown
     * @param int $postletter           If post letters should be shown
     * @return bool
     */
    public static function record_usersettings($userid, $timefilterpast, $timefilterfuture,
                                               $basicletter, $completionletter, $postletter) {
        global $DB;
        $record = new stdClass();
        $record->userid = $userid;
        $record->timefilterpast = $timefilterpast;
        $record->timefilterfuture = $timefilterfuture;
        $record->basicletter = $basicletter;
        $record->completionletter = $completionletter;
        $record->postletter = $postletter;
        // Check if the user already has a record in the database.
        if ($DB->get_record('block_townsquare_preferences', ['userid' => $userid])) {
            // Upgrade the existing record.
            $DB->update_record('block_townsquare_preferences', $record);
            return true;
        }
        $DB->insert_record('block_townsquare_preferences', $record);
        return true;
    }

}
