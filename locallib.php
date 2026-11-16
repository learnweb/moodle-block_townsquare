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
 * Internal library of functions for the townsquare block
 *
 * @package block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to get the color of a letter.
 *
 * @param string $lettertype The type of the letter that wants to retrieve its color setting.
 * @return string The color of the letter.
 * @throws dml_exception
 * @throws moodle_exception
 */
function block_townsquare_get_colorsetting(string $lettertype): string {
    return match ($lettertype) {
        'basicletter' => get_config('block_townsquare', 'basiclettercolor'),
        'postletter' => get_config('block_townsquare', 'postlettercolor'),
        'completionletter' => get_config('block_townsquare', 'completionlettercolor'),
        default => throw new moodle_exception('invalidlettertype', 'block_townsquare'),
    };
}
