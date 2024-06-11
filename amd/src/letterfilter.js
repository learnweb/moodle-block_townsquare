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
            // Get the letter name associated with the checkbox.
            const lettername = checkbox.id;

            // Get all letters that are "activated".
            // Activated means that all filters accept the letter and want to show it.
            const letters = document.querySelectorAll('.townsquare_letter.' + lettername +
                                                      '.ts_timefilter_active.ts_coursefilter_active');

            // Loop through each letter and hide/show based on checkbox state.
            letters.forEach(function(letter) {
                if (checkbox.checked) {
                    letter.classList.add('ts_letterfilter_active'); // Mark the letter as "active".
                } else {
                    letter.classList.remove('ts_letterfilter_active'); // Mark the letter as "not active".
                }
            });
        });
    });
}