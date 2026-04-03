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
 * Townsquare external functions and service definitions.
 *
 * @package    block_townsquare
 * @category   external
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

$functions = [
    'block_townsquare_record_usersettings' => [
        'classname' => 'block_townsquare\external\record_usersettings',
        'methodname' => 'execute',
        'classpath' => 'blocks/townsquare/classes/external/record_usersettings.php',
        'description' => 'Records the user settings for the townsquare block',
        'type' => 'write',
        'ajax' => true,
    ],
    'block_townsquare_reset_usersettings' => [
        'classname' => 'block_townsquare\external\reset_usersettings',
        'methodname' => 'execute',
        'classpath' => 'blocks/townsquare/classes/external/reset_usersettings.php',
        'description' => 'Resets the user settings for the townsquare block',
        'type' => 'write',
        'ajax' => true,
    ],
    'block_townsquare_reload' => [
        'classname' => 'block_townsquare\external\reload',
        'methodname' => 'execute',
        'classpath' => 'blocks/townsquare/classes/external/reload.php',
        'description' => 'Rebuilds the cache',
        'type' => 'write',
        'ajax' => true,
    ],
];
