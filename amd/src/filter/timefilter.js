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
 * JavaScript for the time filter
 *
 * The time filter shows the letters of the last and the next days, e.g. of the last and the next week.
 * "All time" uses the longest time span.
 *
 * @module     block_townsquare/filter/timefilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {refresh} from 'block_townsquare/filter/filtercontroller';

const Selectors = {
    button: '.ts_time_button',
    selected: '.ts_time_button:checked',
    letter: '.townsquare_letter',
    date: '.townsquareletter_date',
};

/**
 * Init function. Applies the time filter when another time span is selected.
 */
export function init() {
    document.querySelectorAll(Selectors.button).forEach(button => button.addEventListener('change', apply));
}

/**
 * Applies the time filter to all letters: a letter is "approved" if it is within the selected time span before or
 * after now. Is also used after the content was reloaded.
 */
export function apply() {
    const now = Date.now() / 1000;
    const timespan = Number(document.querySelector(Selectors.selected).dataset.timespan);
    document.querySelectorAll(Selectors.letter).forEach(letter => {
        const created = Number(letter.querySelector(Selectors.date).dataset.created);
        letter.classList.toggle('ts_timefilter_approved', Math.abs(created - now) <= timespan);
    });
    refresh();
}
