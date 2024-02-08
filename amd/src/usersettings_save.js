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
 * Javascript to save the user settings in the database.
 *
 * This file implements 1 functionality:
 * - If the "save settings" button is pressed, store the settings in the database.
 *
 * @module     block_townsquare/timefilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get the save button for the user settings.
const savebutton = document.getElementById('ts_usersettings_savebutton');

/**
 * Init function
 */
export function init() {
    // Add event listener to the save button.
    savebutton.addEventListener('click', function(button) {
        // First step: collect the current settings.

        // Get the relevant time spans of the time filter.
        let timespans = collecttimefiltersettings();

        // Get the setting of the letter filter checkboxes.
        let letterfilters = collectletterfiltersettings();

        // Second step: store the usersettings in the database.atu
    });
}

/**
 * Function to collect the letter filter settings.
 * @returns {{completionletter: boolean, basicletter: boolean, postletter: boolean}}
 */
function collectletterfiltersettings() {
    let settings = {'basicletter': false, 'completionletter': false, 'postletter':false };

    const checkboxes = document.querySelectorAll('.ts_letter_checkbox');

    checkboxes.forEach(function(checkbox) {
        if (checkbox.checked) {
            switch(checkbox.id) {
                case "basicletter":
                    settings['basicletter'] = true;
                    break;
                case "completionletter":
                    settings['completionletter'] = true;
                    break;
                case "postletter":
                    settings['postletter'] = true;
                    break;

            }
        }
    });
    return settings;
}

/**
 * Function to collect the time filter settings.
 * @returns {{timepast: number, timefuture: number}}
 */
function collecttimefiltersettings() {
    let settings = { timepast: 0, timefuture: 0};

    // Get the relevant time spans of the time filter.
    const alltimebutton = document.querySelectorAll('.ts_all_time_button');
    const futureradiobuttons = document.querySelectorAll('.ts_future_time_button');
    const pastradiobuttons = document.querySelectorAll('.ts_past_time_button');

    // Check if the alltimebutton is set.
    alltimebutton.foreach(function(button) {
       if (button.parentNode.classList.contains('active')) {
           // Get the timespan.
           settings['timepast'] = convertidtotime(button.id);
           settings['timefuture'] = convertidtotime(button.id);
           return settings;
       }
    });

    // If the alltimebutton is not set, check which of the future/past buttons is set.
    futureradiobuttons.foreach(function(button) {
        if (button.parentNode.classList.contains('active')) {
            // Get the timespan.
            settings['timefuture'] = convertidtotime(button.id);
        }
    });

    pastradiobuttons.foreach(function(button) {
        if (button.parentNode.classList.contains('active')) {
            // Get the timespan.
            settings['timepast'] = convertidtotime(button.id);
        }
    });
    return settings;
}


/**
 * Function to convert the radio button id to a useable time span.
 * @param {string} id  The id of the radio button
 * @returns {number}
 */
function convertidtotime(id) {
    // TODO: Please use global functions if possible.
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