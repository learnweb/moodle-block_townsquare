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
 * @module     block_townsquare/usersettings_save
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

// Get the save button for the user settings.
const savebutton = document.getElementById('ts_usersettings_savebutton');

// Get the buttons from the time filter.
const alltimebutton = document.querySelectorAll('.ts_all_time_button');
const futureradiobuttons = document.querySelectorAll('.ts_future_time_button');
const pastradiobuttons = document.querySelectorAll('.ts_past_time_button');

// Get the checkboxes from the letter filter.
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
        // Get the relevant time spans of the time filter and the setting of the letter filter checkboxes.
        let timespans = collecttimefiltersettings();
        let letterfilter = collectletterfiltersettings();

        // Second step: store the usersettings in the database.
        await saveusersettings(userid, timespans['timepast'], timespans['timefuture'], letterfilter['basicletter'],
            letterfilter['completionletter'], letterfilter['postletter']);
    });
}

/**
 * Function to save the user settings in the database.
 * @param {number} userid
 * @param {number} timefilterpast
 * @param {number} timefilterfuture
 * @param {number} basicletter
 * @param {number} completionletter
 * @param {number} postletter
 * @returns {Promise<*>}
 */
function saveusersettings(userid, timefilterpast, timefilterfuture, basicletter, completionletter, postletter) {
    let result;

    const data = {
        methodname: 'block_townsquare_record_usersettings',
        args: {
            userid: userid,
            timefilterpast: timefilterpast,
            timefilterfuture: timefilterfuture,
            basicletter: basicletter,
            completionletter: completionletter,
            postletter: postletter,
        },
    };
    result = Ajax.call([data]);
    console.log('HI I AM HERE');
    // Make the clicked button green by adding a class.
    savebutton.classList.add('bg-success', 'text-white', 'ts_button_transition');

    // Remove the classes after one second.
    setTimeout(function() {
        savebutton.classList.remove('bg-success');
        savebutton.classList.remove('text-white');
    }, 1500);
    return result;

}

/**
 * Function to execute existing user settings when loading the townsquare.
 * @param {Object} settingsfromdb
 */
function executeusersettings(settingsfromdb) {

    // First step: set the time filter settings.
    // Change the time into the correct radio button id.
    let futurebuttonid = converttimetoid(settingsfromdb['timefilterfuture'], true);
    let pastbuttonid = converttimetoid(settingsfromdb['timefilterpast'], false);

    // If the time span is a combination of past and future, go through the two radio buttons and activate the filter.
    if (futurebuttonid !== "ts_time_all") {
        futureradiobuttons.forEach(function(button) {
            if (button.id === futurebuttonid) {
                button.parentNode.classList.add('active');
                button.checked = true;
                button.dispatchEvent(new Event('change'));
                alltimebutton.forEach(function(alltimebutton) {
                    alltimebutton.checked = false;
                    alltimebutton.parentNode.classList.remove('active');
                });
            }
        });
        pastradiobuttons.forEach(function(button) {
            if (button.id === pastbuttonid) {
                button.parentNode.classList.add('active');
                button.checked = true;
                button.dispatchEvent(new Event('change'));
                alltimebutton.forEach(function(alltimebutton) {
                    alltimebutton.checked = false;
                    alltimebutton.parentNode.classList.remove('active');
                });
            }
        });
    } else {
        // If the time span is set to all time, activate the all time button.
        alltimebutton.forEach(function(button) {
            button.parentNode.classList.add('active');
            button.checked = true;
            button.dispatchEvent(new Event('change'));
        });
    }

    // Second step: set the letter filter settings.
    // Per default all checkboxes are checked. If the setting is 0, uncheck the checkbox.
    checkboxes.forEach(function(checkbox) {
        let basiclettercheck = checkbox.id === 'basicletter' && settingsfromdb['basicletter'] === "";
        let completionlettercheck = checkbox.id === 'completionletter' && settingsfromdb['completionletter'] === "0";
        let postlettercheck = checkbox.id === 'postletter' && settingsfromdb['postletter'] === "0";

        if (basiclettercheck || completionlettercheck || postlettercheck) {
            checkbox.click();
        }
    });
}

/**
 * Function to collect the letter filter settings.
 * @returns {{basicletter: number, completionletter: number, postletter: number}}
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
    return settings;
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
 * @param {string} time
 * @param {boolean} future
 * @returns {string}
 */
function converttimetoid(time, future) {
    switch (time) {
        case "15778463":
            return "ts_time_all";
        case "172800":
            if (future) {
                return "ts_time_next_twodays";
            }
            return "ts_time_past_twodays";
        case "432000":
            if (future) {
                return "ts_time_next_fivedays";
            }
            return "ts_time_last_fivedays";
        case "604800":
            if (future) {
                return "ts_time_next_week";
            }
            return "ts_time_last_week";
        case "2592000":
            if (future) {
                return "ts_time_next_month";

            }
            return "ts_time_last_month";
    }
}
