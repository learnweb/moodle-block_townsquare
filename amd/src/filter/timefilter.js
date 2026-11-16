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
 * This file implements 1 functionality:
 * - Checks, which of the radio buttons is pressed and filters the content based on the time.
 *   One past and one future time span can be combined. "All notifications" deselects both.
 *
 * @module     block_townsquare/filter/timefilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get the relevant radio buttons.
const alltimebutton = document.getElementById('ts_time_all');
const futureradiobuttons = document.querySelectorAll('.ts_future_time_button');
const pastradiobuttons = document.querySelectorAll('.ts_past_time_button');

/**
 * Init function. Adds event listeners to all time filter buttons.
 */
export function init() {
    alltimebutton.addEventListener('change', function() {
        // Disable all other radio buttons that filter more specific times.
        [...futureradiobuttons, ...pastradiobuttons].forEach(button => {
            button.checked = false;
        });
        apply();
    });

    [...futureradiobuttons, ...pastradiobuttons].forEach(function(button) {
        button.addEventListener('change', function() {
            // Disable the all_time button.
            alltimebutton.checked = false;
            apply();
        });
    });
}

/**
 * Applies the time filter to all letters based on the selected time filter buttons.
 * Is also used after the content was reloaded.
 */
export function apply() {
    const currenttime = new Date().getTime() / 1000;
    const timespans = [];

    if (alltimebutton.checked) {
        const timespan = Number(alltimebutton.dataset.timespan);
        timespans.push([currenttime - timespan, currenttime + timespan]);
    } else {
        futureradiobuttons.forEach(function(button) {
            if (button.checked) {
                timespans.push([currenttime, currenttime + Number(button.dataset.timespan)]);
            }
        });
        pastradiobuttons.forEach(function(button) {
            if (button.checked) {
                timespans.push([currenttime - Number(button.dataset.timespan), currenttime]);
            }
        });
    }

    // Loop through each letter and mark it as "approved" if it is in one of the time spans.
    document.querySelectorAll('.townsquare_letter').forEach(function(letter) {
        const lettertime = Number(letter.querySelector('.townsquareletter_date').dataset.created);
        const approved = timespans.some(([start, end]) => lettertime >= start && lettertime <= end);
        letter.classList.toggle('ts_timefilter_approved', approved);
    });
}
