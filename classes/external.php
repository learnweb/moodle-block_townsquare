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
namespace block_townsquare;
use external_function_parameters;
use Exception;
use external_api;
use external_value;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/externallib.php');
require_once("$CFG->libdir/externallib.php");

/**
 * Class implementing the external API, esp. for AJAX functions.
 *
 * @package    block_townsquare
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function reset_usersettings_parameters() {
        return new external_function_parameters(['userid' => new external_value(PARAM_INT, 'the user id')]);
    }

    /**
     * Return the result of the reset_usersettings function
     * @return external_value
     */
    public static function reset_usersettings_returns(): external_value {
        return new external_value(PARAM_BOOL, 'true if successful');
    }

    /**
     * Reset the user settings
     *
     * @param $userid
     * @return bool
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function reset_usersettings($userid) {
        global $DB;

        // Parameter validation.
        if (!self::validate_parameters(self::reset_usersettings_parameters(), ['userid' => $userid])) {
            return false;
        }

        $transaction = $DB->start_delegated_transaction();

        // Check if there is a record in the database with the userid and delete it.
        if ($records = $DB->get_records('block_townsquare_preferences', ['userid' => $userid])) {
            try {
                foreach ($records as $record) {
                    $DB->delete_records('block_townsquare_preferences', ['id' => $record->id]);
                }
            } catch (Exception $e) {
                $transaction->rollback($e);
                return false;
            }
            $transaction->allow_commit();
            return true;
        }
        return true;
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function record_usersettings_parameters() {
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
    public static function record_usersettings_returns(): external_value {
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
                                               $basicletter, $completionletter, $postletter): bool {
        global $DB;
        // Parameter validation.
        $params = self::validate_parameters(self::record_usersettings_parameters(), [
            'userid' => $userid, 'timefilterpast' => $timefilterpast, 'timefilterfuture' => $timefilterfuture,
            'basicletter' => $basicletter, 'completionletter' => $completionletter, 'postletter' => $postletter,
        ]);

        // Check if the user already has a record in the database.
        if ($records = $DB->get_records('block_townsquare_preferences', ['userid' => $userid])) {
            // If there more than a record (it only should be only one), delete all of them and insert the new one.
            if (count($records) <= 1) {
                // Upgrade the existing record.
                $record = reset($records);
                $record->timefilterpast = $params['timefilterpast'];
                $record->timefilterfuture = $params['timefilterfuture'];
                $record->basicletter = $params['basicletter'];
                $record->completionletter = $params['completionletter'];
                $record->postletter = $params['postletter'];

                $DB->update_record('block_townsquare_preferences', $record);
                return true;
            }
            try {
                foreach ($records as $record) {
                    $DB->delete_records('block_townsquare_preferences', ['id' => $record->id]);
                }
            } catch (Exception $e) {
                return false;
            }
        }
        $record = new stdClass();
        $record->userid = $params['userid'];
        $record->timefilterpast = $params['timefilterpast'];
        $record->timefilterfuture = $params['timefilterfuture'];
        $record->basicletter = $params['basicletter'];
        $record->completionletter = $params['completionletter'];
        $record->postletter = $params['postletter'];
        $DB->insert_record('block_townsquare_preferences', $record);
        return true;
    }
}
