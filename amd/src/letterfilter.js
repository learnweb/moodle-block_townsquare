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
 * Javascript for the letter filter
 *
 * This file implements 1 functionality:
 * - Checks the checkboxes of the letter filter and hides content from courses if the checkbox is not checked.
 *
 * @module     block_townsquare/letterfilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get the relevant checkboxes.
const checkboxes = document.querySelectorAll('.ts_letter_checkbox');

/**
 * Init function
 */
export function init() {
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {

            // Get the letter name associated with the checkbox
            const lettername = checkbox.id;

            // Get all the right letters.
            const letters = document.querySelectorAll('.townsquare_letter.' + lettername);

            // Loop through each letter and hide/show based on checkbox state
            letters.forEach(function(letter) {
                if (checkbox.checked) {
                    letter.style.display = 'block'; // Show the letter
                } else {
                    letter.style.display = 'none'; // Hide the letter
                }
            });
        });
    });
}