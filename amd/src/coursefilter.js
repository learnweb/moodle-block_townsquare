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
 * Javascript for the course filter
 *
 * This file implements 1 functionality:
 * - Checks the checkboxes of the course filter and hides content from courses if the checkbox is not checked.
 *
 * @module     block_townsquare/coursefilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const checkboxes = document.querySelectorAll("input[type=checkbox][name=ts_course_checkbox]");
let enabledSettings = [];

/**
 * Init function
 */
export function init() {
    checkboxes.forEach(
        (element) => {
            element.addEventListener('change', executecoursefilter);
        }
    );
}

/**
 * Checks if the checkboxes are checked.
 */
function executecoursefilter() {
    enabledSettings =
        Array.from(checkboxes) // Convert checkboxes to an array to use filter and map.
            .filter(i => i.checked) // Use Array.filter to remove unchecked checkboxes.
            .map(i => i.value); // Use Array.map to extract only the checkbox values from the array of objects.

    window.alert(enabledSettings);
}