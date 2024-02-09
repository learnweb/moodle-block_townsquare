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

import Ajax from 'core/ajax';

// Get the save button for the user settings.
const savebutton = document.getElementById('ts_usersettings_savebutton');
const alltimebutton = document.querySelectorAll('.ts_all_time_button');
const futureradiobuttons = document.querySelectorAll('.ts_future_time_button');
const pastradiobuttons = document.querySelectorAll('.ts_past_time_button');
const checkboxes = document.querySelectorAll('.ts_letter_checkbox');

/**
 * Init function
 *
 * @param {number} userid           The id of the current user.
 * @param {object} settingsfromdb   The settings from the database, if there are any.
 */
export function init(userid, settingsfromdb) {
    // When the page is loaded, set the settings from the database.
    if (settingsfromdb) {
        executeusersettings(settingsfromdb);
    }

    // Add event listener to the save button.
    savebutton.addEventListener('click', async function() {

        // First step: collect the current settings.
        // Get the relevant time spans of the time filter and the setting of the letter filter checkboxes..
        let timespans = collecttimefiltersettings();
        let letterfilter = collectletterfiltersettings();

        // Second step: store the usersettings in the database.
        await saveusersettings(userid, timespans['timepast'], timespans['timefuture'], letterfilter);
    });
}

/**
 * Function to save the user settings in the database.
 * @param {number} userid
 * @param {number} timefilterpast
 * @param {number} timefilterfuture
 * @param {number} letterfilter
 * @returns {Promise<*>}
 */
async function saveusersettings(userid, timefilterpast, timefilterfuture, letterfilter) {
    window.alert('Settings here.');
    window.alert(userid);
    window.alert(timefilterpast);
    window.alert(timefilterfuture);
    window.alert(letterfilter);
    let result = await Ajax.call([{
        methodname: 'block_townsquare_record_usersettings',
        args: {
            userid: userid,
            timefilterpast: timefilterpast,
            timefilterfuture: timefilterfuture,
            letterfilter: letterfilter
        }
    }]);
    window.alert('im done');
    return result;
}

/**
 * Function to execute existing user settings when loading the townsquare.
 * @param {number} settingsfromdb
 */
function executeusersettings(settingsfromdb) {
    // Load the settings.
    let timefilterfuture = settingsfromdb['timefilterfuture'];
    let timefilterpast = settingsfromdb['timefilterpast'];
    let letterfilter = settingsfromdb['letterfilter'];

    // First step: set the time filter settings.
    // Change the time into the correct radio button id.
    let futurebuttonid = converttimetoid(timefilterfuture, true);
    let pastbuttonid = converttimetoid(timefilterpast, false);
    // If the time span is a combination of past and future, go through the two radio buttons and click them to activate the filter.
    if (futurebuttonid !== "ts_time_all") {
        futureradiobuttons.forEach(function(button) {
            if (button.id === futurebuttonid) {
                button.click();
            }
        });
        pastradiobuttons.forEach(function(button) {
            if (button.id === pastbuttonid) {
                button.click();
            }
        });
    } else {
        // If the time span is the all time filter, click the all time button.
        alltimebutton.forEach(function(button) {
            if (button.id === futurebuttonid) {
                button.click();
            }
        });
    }

    // Second step: set the letter filter settings.
    let letterfilters = convertletterfiltersetting(letterfilter);
    checkboxes.forEach(function(checkbox) {
        if (checkbox.id === "basicletter" && letterfilters['basicletter'] === 1) {
            checkbox.click();
            checkbox.checked = true;
        } else if (checkbox.id === "completionletter" && letterfilters['completionletter'] === 1) {
            checkbox.click();
            checkbox.checked = true;
        } else if (checkbox.id === "postletter" && letterfilters['postletter'] === 1) {
            checkbox.click();
            checkbox.checked = true;
        }
    });
}

/**
 * Function to collect the letter filter settings.
 * @returns {number} The setting number.
 */
function collectletterfiltersettings() {
    let settings = {'basicletter': 0, 'completionletter': 0, 'postletter': 0 };

    checkboxes.forEach(function(checkbox) {
        if (checkbox.checked) {
            switch(checkbox.id) {
                case "basicletter":
                    settings['basicletter'] = 1;
                    break;
                case "completionletter":
                    settings['completionletter'] = 1;
                    break;
                case "postletter":
                    settings['postletter'] = 1;
                    break;

            }
        }
    });
    // Calculate the setting number. It is a number between 0 and 7, and each letter represents a bit.
    return 4 * settings['basicletter'] + 2 * settings['completionletter'] + 1 * settings['postletter'];
}

/**
 * Function to collect the time filter settings.
 * @returns {{timepast: number, timefuture: number}}
 */
function collecttimefiltersettings() {
    let settings = { timepast: 0, timefuture: 0};
    let settingsset = false;

    // Get the relevant time spans of the time filter.
    // Check if the alltimebutton is set.
    alltimebutton.forEach(function(button) {
       if (button.parentNode.classList.contains('active')) {
           // Get the timespan.
           settings['timepast'] = convertidtotime(button.id);
           settings['timefuture'] = convertidtotime(button.id);
           settingsset = true;
       }
    });

    if (settingsset) {
        return settings;
    }

    // If the alltimebutton is not set, check which of the future/past buttons is set.
    futureradiobuttons.forEach(function(button) {
        if (button.parentNode.classList.contains('active')) {
            // Get the timespan.
            settings['timefuture'] = convertidtotime(button.id);
        }
    });

    pastradiobuttons.forEach(function(button) {
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

/**
 * Function to convert the time span to a radio button id.
 * @param {number} time
 * @param {bool} future
 * @returns {string}
 */
function converttimetoid(time, future) {
    switch (time) {
        case 15778463:
            return "ts_time_all";
        case 172800:
            if (future) {
                return "ts_time_next_twodays";
            }
            return "ts_time_past_twodays";
        case 432000:
            if (future) {
                return "ts_time_next_fivedays";
            }
            return "ts_time_last_fivedays";
        case 604800:
            if (future) {
                return "ts_time_next_week";
            }
            return "ts_time_last_week";
        case 2592000:
            if (future) {
                return "ts_time_next_month";

            }
            return "ts_time_last_month";
    }
}

/**
 * Converts the number of the letter filter to an object that has the activated letter filters.
 * @param {number} settingnumber
 */
function convertletterfiltersetting(settingnumber) {
    let settings = {'basicletter': 0, 'completionletter': 0, 'postletter': 0 };

    // Check if the basicletter is active.
    if (settingnumber >= 4) {
        settings['basicletter'] = 1;
    }

    // Check if the completionletter is active.
    if (settingnumber === 2 || settingnumber === 3 || settingnumber === 6 || settingnumber === 7) {
        settings['completionletter'] = 1;
    }

    // check if the postletter is active.
    if (settingnumber % 2 === 1) {
        settings['postletter'] = 1;
    }

    return settings;

}
