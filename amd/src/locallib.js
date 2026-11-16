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
 * Townsquare library for javascript functions. This file stores little helper functions.
 *
 * @module     block_townsquare/locallib
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to convert the time span to a radio button id.
 * @param {string} time
 * @param {boolean} future
 * @returns {string}
 */
export function convertTimeToId(time, future) {
    switch (time) {
        case "15778463": return "ts_time_all";
        case "172800": return future ? "ts_time_next_twodays" : "ts_time_last_twodays";
        case "432000": return future ? "ts_time_next_fivedays" : "ts_time_last_fivedays";
        case "604800": return future ? "ts_time_next_week" : "ts_time_last_week";
        case "2592000": return future ? "ts_time_next_month" : "ts_time_last_month";
    }
    return "";
}

/**
 * Checks if a time filter radio button is selected.
 *
 * In the Bootstrap 4 side panel (Moodle 4.5) all time radio buttons share one name, so only one of them can be checked.
 * There, the selection is stored as 'active' class on the surrounding label. In the Bootstrap 5 markup, the past and future
 * buttons have their own names and the checked state is enough.
 *
 * @param {HTMLInputElement} button
 * @returns {boolean}
 */
export function isTimeButtonSelected(button) {
    return button.checked || (button.parentNode.tagName === 'LABEL' && button.parentNode.classList.contains('active'));
}

/**
 * Selects or deselects a time filter radio button (see isTimeButtonSelected).
 *
 * @param {HTMLInputElement} button
 * @param {boolean} selected
 */
export function setTimeButtonSelected(button, selected) {
    button.checked = selected;
    if (button.parentNode.tagName === 'LABEL') {
        button.parentNode.classList.toggle('active', selected);
    }
}
