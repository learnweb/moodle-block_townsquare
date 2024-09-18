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
 * Javascript for the time filter
 *
 * This file implements 1 functionality:
 * - Checks, which of the radio buttons is pressed and filters the content based on the time.
 *
 * @module     block_townsquare/timefilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get the relevant radio buttons.
const alltimebutton = document.querySelectorAll('.ts_all_time_button');
const futureradiobuttons = document.querySelectorAll('.ts_future_time_button');
const pastradiobuttons = document.querySelectorAll('.ts_past_time_button');

// Define to change the time span, an additional time span and the current time.
let currenttime;
let timestart;
let timeend;
let addstarttime;
let addendtime;

/**
 * Init function
 */
export function init() {
    // Set the current time.
    currenttime = new Date().getTime() / 1000;

    // Add event listeners to the all kind of buttons.
    alltimeaddEventListener();
    futuretimeaddEventListener();
    pasttimeaddEventListener();
}

/**
 * Function to execute the filter
 * @param {int} starttime   Start of time span for filtering of the current pressed button
 * @param {int} endtime     End of time span for filtering of the current pressed
 * @param {int} addstarttime Start of time span for filtering of an additional radio button.
 * @param {int} addendtime   End of time span for filtering of an additional radio button.
 * @param {boolean} buttonstate State of the radio button (true or false)
 */
function executefilter(starttime, endtime, addstarttime, addendtime, buttonstate) {
    // Get all letters.
    const letters = document.querySelectorAll('.townsquare_letter');

    // Loop through each letter and hide/show based on radiobutton state.
    letters.forEach(function(letter) {

        // Get the created time stamp of each letter.
        let lettertime = letter.querySelector('.townsquareletter_date').id;

        // If the radio button is checked and the letter is in the time span, activate it.
        if ((buttonstate && (lettertime >= starttime && lettertime <= endtime)) ||
            (lettertime >= addstarttime && lettertime <= addendtime)) {
            letter.classList.add('ts_timefilter_approved'); // Mark the letter as "approved".
        } else {
            letter.classList.remove('ts_timefilter_approved'); // Mark the letter as "not approved".
        }
    });
}

/**
 * Function to add event listeners to the all_time button.
 */
function alltimeaddEventListener() {
    alltimebutton.forEach(function(button) {
        button.addEventListener('change', function() {
            // Set the time span to show all letters.
            timestart = currenttime - convertidtotime(button.id);
            timeend = currenttime + convertidtotime(button.id);
            addstarttime = 0;
            addendtime = 0;

            // Disable all other radio buttons that filter more specific times.
            futureradiobuttons.forEach(function(futureradiobutton) {
                futureradiobutton.checked = false;
                futureradiobutton.parentNode.classList.remove("active");
            });
            pastradiobuttons.forEach(function(pastradiobutton) {
                pastradiobutton.checked = false;
                pastradiobutton.parentNode.classList.remove("active");

            });

            // Execute the filter function.
            executefilter(timestart, timeend, addstarttime,addendtime, button.checked);
        });
    });
}

/**
 * Function to add event listeners to the future time radio buttons.
 */
function futuretimeaddEventListener() {
    futureradiobuttons.forEach(function(button) {
        button.addEventListener('change', function() {
            // Disable the all_time button.
            alltimebutton.forEach(function(alltimebutton) {
                alltimebutton.checked = false;
                alltimebutton.parentNode.classList.remove('active');
            });

            // Set the time span based on the radiobutton id.
            timestart = currenttime;
            timeend = currenttime + convertidtotime(button.id);

            // Check if one past time button is checked. If yes, set the additional time span based on its id.
            addstarttime = 0;
            addendtime = 0;
            pastradiobuttons.forEach(function(pastradiobutton) {
                if (pastradiobutton.parentNode.classList.contains('active')) {
                    addstarttime = currenttime - convertidtotime(pastradiobutton.id);
                    addendtime = currenttime;
                }
            });

            // Execute the filter function.
            executefilter(timestart, timeend, addstarttime, addendtime, button.checked);
        });
    });
}

/**
 * Function to add event listeners to the past time radio buttons.
 */
function pasttimeaddEventListener() {
    pastradiobuttons.forEach(function(button) {
        button.addEventListener('change', function() {
            // Disable the all_time button.
            alltimebutton.forEach(function(alltimebutton) {
                alltimebutton.checked = false;
                alltimebutton.parentNode.classList.remove('active');
            });

            // Set the time span based on the radiobutton id.
            timestart = currenttime - convertidtotime(button.id);
            timeend = currenttime;

            // Check if one future time button is checked. If yes, set the additional time span based on its id.
            addstarttime = 0;
            addendtime = 0;
            futureradiobuttons.forEach(function(futureradiobutton) {
                if (futureradiobutton.parentNode.classList.contains('active')) {
                    addstarttime = currenttime;
                    addendtime = currenttime + convertidtotime(futureradiobutton.id);
                }
            });

            // Execute the filter function.
            executefilter(timestart, timeend, addstarttime, addendtime, button.checked);
        });
    });
}

/**
 * Function to convert the radio button id to a useable time span.
 * @param {string} id  The id of the radio button
 * @returns {number}
 */
function convertidtotime(id) {
    switch(id) {
        case "ts_time_all":
            return 15778463;
        case "ts_time_next_twodays":
        case "ts_time_last_twodays":
            return 172800;
        case "ts_time_next_fivedays":
        case "ts_time_last_fivedays":
            return 432000;
        case "ts_time_next_week":
        case "ts_time_last_week":
            return 604800;
        case "ts_time_next_month":
        case "ts_time_last_month":
            return 2592000;
    }
}
