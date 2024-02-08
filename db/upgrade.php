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
 * Plugin upgrade steps are defined here.
 *
 * @package     block_townsquare
 * @category    upgrade
 * @copyright   2023 Tamaro Walter
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute block_townsquare upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_block_townsquare_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();

    // For further information please read {@link https://docs.moodle.org/dev/Upgrade_API}.
    //
    // You will also have to create the db/install.xml file by using the XMLDB Editor.
    // Documentation for the XMLDB Editor can be found at {@link https://docs.moodle.org/dev/XMLDB_editor}.

    if ($oldversion < 2024020700) {
        // Define new table townsquare_usersettings to be created.
        $table = new xmldb_table('townsquare_usersettings');

        // Adding fields to table townsquare_usersettings.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timefilterpast', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timefilterfuture', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('letterfilter', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table townsquare_usersettings.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for townsquare_usersettings.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Moodleoverflow savepoint reached.
        upgrade_block_savepoint(true, 2024020700, 'townsquare');
    }

    return true;
}
