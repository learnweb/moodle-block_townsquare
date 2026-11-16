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
 * JavaScript for the course filter
 *
 * This file implements 1 functionality:
 * - Checks the checkboxes of the course filter and hides content from courses if the checkbox is not checked.
 *
 * @module     block_townsquare/filter/coursefilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get the relevant checkboxes.
const checkboxes = document.querySelectorAll('.ts_course_checkbox');

/**
 * Init function. Adds an event listener to the course filter checkboxes. This is part of the content filtering functionality.
 * Every checkbox represents one course that shows content in Town square.
 */
export function init() {
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', apply);
    });
}

/**
 * Applies the course filter to all letters. Letters from courses whose checkbox is not checked are not "approved".
 * Is also used after the content was reloaded.
 */
export function apply() {
    // Collect the ids of all courses that are not selected.
    const hiddencourses = [...checkboxes]
        .filter(checkbox => !checkbox.checked)
        .map(checkbox => Number(checkbox.dataset.courseid));

    // Loop through each letter and mark it as "approved" or not based on its course.
    document.querySelectorAll('.townsquare_letter').forEach(function(letter) {
        const lettercourseid = Number(letter.querySelector('.townsquareletter_course').dataset.courseid);
        letter.classList.toggle('ts_coursefilter_approved', !hiddencourses.includes(lettercourseid));
    });
}
