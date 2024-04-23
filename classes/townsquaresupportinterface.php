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
 * Interface for the sub-plugin townsquaresupport.
 *
 * The Plugins of the type townsquaresupport are used to increase the content of the townsquare block.
 * Every module that wants to show content on townsquare can implement this interface.
 * Every module must:
 * - gather "events" for a user that can be transformed into letters.
 * - provide php_unit test to ensure the correct behaviour.
 * The townsquare block will call the export function of the (custom) letter and draw the letter on the dashboard.
 *
 * @package   block_townsquare
 * @copyright 2023 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_townsquaresupport\townsquaresupportinterface;

interface townsquaresupportinterface {

    /**
     * Function to gather the events
     * Every event must gain sufficient data so that townsquare can build a letter from it.
     * The array should contain following information:
     * [courseid] => int           Course ID from where the content comes from.
     * [modulename] => string      Name of the activity module.
     * [instancename] => string    Name of the instance that shows the notification.
     * [content] => string         The content that will be showed in the letter.
     * [timestart] => int          Timestamp that represents the deadline/creation of a notification. Is important to sort events.
     * [coursemoduleid] => int     Course module id of the content module.
     * [eventtype] => string       Type of the event.
     *
     * @return array of events that can be transformed into letters
     */
    public function get_events(): array;
}
