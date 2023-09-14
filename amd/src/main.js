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
 * Main Javascript functions for the townsquare block.
 *
 * @module     block_townsquare/main
 * @copyright  2023 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//import Ajax from 'core/ajax';
//import Prefetch from 'core/prefetch';
//import {get_string as getString} from 'core/str';

// Define const.

/**
 * Init function
 */
export function init() {
    getTime();
    setInterval(getTime, 1000);
}

/**
 * Function that shows the current Time.
 */
function getTime() {
    var today = new Date();
    var time = today.getUTCHours() + ":" + today.getUTCMinutes() + ":" + today.getUTCSeconds();
    document.getElementById('townsquare_time').innerHTML = time;
}
