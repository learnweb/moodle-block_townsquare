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
use core_external\restricted_context_exception;
use dml_exception;
use external_function_parameters;
use Exception;
use external_api;
use external_value;
use invalid_parameter_exception;
use context_user;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/externallib.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Class implementing the external API, esp. for AJAX functions.
 *  Resets usersettings in the database.
 *
 * @package    block_townsquare
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reset_usersettings extends external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(['userid' => new external_value(PARAM_INT, 'the user id')]);
    }

    /**
     * Return the result of the reset_usersettings function
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_BOOL, 'true if successful');
    }

    /**
     * Reset the user settings
     *
     * @param int $userid
     * @return bool
     * @throws dml_exception
     * @throws invalid_parameter_exception|restricted_context_exception
     */
    public static function execute(int $userid): bool {
        global $DB;

        // Parameter validation.
        if (!self::validate_parameters(self::execute_parameters(), ['userid' => $userid])) {
            return false;
        }

        self::validate_context(context_user::instance($userid));

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
}
