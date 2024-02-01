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

//const checkboxes = document.querySelectorAll("input[type=checkbox][name=ts_course_checkbox]");
const checkboxes = document.querySelectorAll('.ts_course_checkbox');

/**
 * Init function
 */
export function init() {
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            // Get the courseid associated with the checkbox
            const courseid = checkbox.id;

            // Get all letters with the corresponding courseid
            const letters = document.querySelectorAll('.townsquare_letter');

            // Loop through each letter and hide/show based on checkbox state
            letters.forEach(function(letter) {
                let letterCourseId = letter.querySelector('.townsquareletter_course').id;

                if (courseid === letterCourseId) {
                    if (checkbox.checked) {
                        letter.style.display = 'block'; // Show the letter
                    } else {
                        letter.style.display = 'none'; // Hide the letter
                    }
                }
            });
        });
    });
}
