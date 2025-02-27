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
 * Privacy Provider for block_townsquare.
 * @package   block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_townsquare\privacy;

use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
use stdClass;


/**
 * Class that describes the type of data that is stored.
 *
 * @package   block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    core_userlist_provider {

    /**
     * Function that describes the type of data that is stored.
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('block_townsquare', [
            'userid' => 'privacy:metadata:block_townsquare_preferences:userid',
            'timefilterpast' => 'privacy:metadata:block_townsquare_preferences:timefilterpast',
            'timefilterfuture' => 'privacy:metadata:block_townsquare_preferences:timefilterfuture',
            'basicletter' => 'privacy:metadata:block_townsquare_preferences:basicletter',
            'completionletter' => 'privacy:metadata:block_townsquare_preferences:completionletter',
            'postletter' => 'privacy:metadata:block_townsquare_preferences:postletter',
            ], 'privacy:metadata:block_townsquare_preferences');
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT context.id
                FROM {block_townsquare_preferences} preferences
                JOIN {user} u
                    ON preferences.userid = u.id
                JOIN {context} context
                    ON context.instanceid = u.id
                        AND context.contextlevel = :contextlevel
                WHERE preferences.userid = :userid";

        $params = ['userid' => $userid, 'contextlevel' => CONTEXT_USER];

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        $sql = "SELECT userid
                FROM {block_townsquare_preferences}
                WHERE userid = ?";
        $params = [$context->instanceid];
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $townsquaredata = [];
        $results = $DB->get_records('block_townsquare_preferences', ['userid' => $contextlist->get_user()->id]);

        foreach ($results as $result) {
            $data = new stdClass();
            $data->userid = $result->userid;
            $data->timefilterpast = $result->timefilterpast;
            $data->timefilterfuture = $result->timefilterfuture;
            $data->basicletter = $result->basicletter;
            $data->completionletter = $result->completionletter;
            $data->postletter = $result->postletter;

            $townsquaredata[] = $data;
        }
        if (!empty($townsquaredata)) {
            writer::with_context($contextlist->current())->export_data([
                get_string('pluginname', 'block_townsquare'), ], (object) $townsquaredata);
        }
    }

    /**
     * Delete multiple user data within a single context.
     * @param approved_userlist $userlist   The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context instanceof \context_user &&
            in_array($context->instanceid, $userlist->get_userids())) {
            $DB->delete_records('block_townsquare_preferences', ['userid' => $context->instanceid]);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     * @param \context $context     The specific context to delete data for.
     * @return void
     * @throws \dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context instanceof \context_user) {
            $DB->delete_records('block_townsquare_preferences', ['userid' => $context->instanceid]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     * @param approved_contextlist $contextlist     The approved contexts and user information to delete information for.
     * @return void
     * @throws \dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        foreach ($contextlist as $context) {
            if ($context->contextlevel == CONTEXT_USER && $contextlist->get_user()->id == $context->instanceid) {
                $DB->delete_records('block_townsquare_preferences', ['userid' => $contextlist->get_user()->id]);
            }
        }
    }

}
