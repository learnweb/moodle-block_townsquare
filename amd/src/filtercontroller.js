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
 * JavaScript to show/hide letters based on all filters
 *
 * @module     block_townsquare/filtercontroller
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get all letters from townsquare.
const letters = document.querySelectorAll('.townsquare_letter');

/**
 * Init function. Controls the visibility of letters based on the approval of all filters.
 */
export function init() {
    // First step: activate every letter by adding the filter classes.
    letters.forEach(function(letter) {
        letter.classList.add('ts_coursefilter_approved');
        letter.classList.add('ts_timefilter_approved');
        letter.classList.add('ts_letterfilter_approved');
    });

    // Add a mutation listener to each letter.
    letters.forEach(function(letter) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    // If the class of the letter changes, check if the letter should be shown or hidden.
                    let coursefilter = letter.classList.contains('ts_coursefilter_approved');
                    let timefilter = letter.classList.contains('ts_timefilter_approved');
                    let letterfilter = letter.classList.contains('ts_letterfilter_approved');

                    // If all filters approve the letter, show the letter.
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
