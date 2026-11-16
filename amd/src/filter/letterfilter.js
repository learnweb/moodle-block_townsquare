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
 * JavaScript for the letter filter
 *
 * @module     block_townsquare/filter/letterfilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get the relevant checkboxes.
const checkboxes = document.querySelectorAll('.ts_letter_checkbox');

/**
 * Init function. Adds an event listener to the letter filter checkboxes. This is part of the content filtering functionality.
 * Every checkbox represents a kind of letter (posts, activity completions, basic notifications) that shows content in Town square.
 */
export function init() {
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', apply);
    });
}

/**
 * Applies the letter filter to all letters. Every checkbox id is the class name of a kind of letter.
 * Is also used after the content was reloaded.
 */
export function apply() {
    checkboxes.forEach(function(checkbox) {
        document.querySelectorAll('.townsquare_letter.' + checkbox.id).forEach(function(letter) {
            letter.classList.toggle('ts_letterfilter_approved', checkbox.checked);
        });
    });
}
