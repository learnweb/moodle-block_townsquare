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
 * Javascript to show/hide letters based on all filters
 *
 * This file implements 1 functionality:
 * - If the "save settings" button is pressed, store the settings in the database.
 *
 * @module     block_townsquare/filtercontroller
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get all letters from townsquare.
const letters = document.querySelectorAll('.townsquare_letter');

/**
 * Init function
 */
export function init() {
    // First step: activate every letter by adding the filter classes.
    letters.forEach(function(letter) {
        letter.classList.add('ts_coursefilter_active');
        letter.classList.add('ts_timefilter_active');
        letter.classList.add('ts_letterfilter_active');
    });

    // Add a mutation listener to each letter.
    letters.forEach(function(letter) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    // If the class of the letter changes, check if the letter should be shown or hidden.
                    let coursefilter = letter.classList.contains('ts_coursefilter_active');
                    let timefilter = letter.classList.contains('ts_timefilter_active');
                    let letterfilter = letter.classList.contains('ts_letterfilter_active');

                    // If all filters are active, show the letter.
                    if (coursefilter && timefilter && letterfilter) {
                        letter.style.display = 'block';
                    } else {
                        letter.style.display = 'none';
                    }
                }
            });
        });

        observer.observe(letter, {attributes: true});
    });
}