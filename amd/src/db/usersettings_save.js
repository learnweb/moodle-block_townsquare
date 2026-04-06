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
 * JavaScript to save the user settings in the database.
 *
 * This file implements 1 functionality:
 * - If the "save settings" button is pressed, store the settings in the database.
 *
 * @module     block_townsquare/db/usersettings_save
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getString} from "core/str";
import Notification from 'core/notification';
import {convertIdToTime, convertTimeToId} from 'block_townsquare/locallib';

// Get the save button for the user settings.
const savebutton = document.getElementById('ts_usersettings_savebutton');

// Get the buttons from the time filter.
const alltimebutton = document.getElementById('ts_time_all');
const futureradiobuttons = document.querySelectorAll('.ts_future_time_button');
const pastradiobuttons = document.querySelectorAll('.ts_past_time_button');

// Get the checkboxes from the letter filter.
const letterCheckboxes = document.querySelectorAll('.ts_letter_checkbox');

// Get the checkboxes from the course filter.
const courseCheckboxes = document.querySelectorAll('.ts_course_checkbox');

/**
 * Init function. This functions adapts the filter settings to the user settings from the database. If the user changes settings
 * and clicks the save button, the settings are stored in the database.
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
        let courses = collectcoursesettings(settingsfromdb.courses);

        const data = {
            methodname: 'block_townsquare_record_usersettings',
            args: {
                userid: userid,
                timefilterpast: timespans.timepast,
                timefilterfuture: timespans.timefuture,
                basicletter: letterfilter.basicletter,
                completionletter: letterfilter.completionletter,
                postletter: letterfilter.postletter,
                courses: courses,
            },
        };
        const result = await Ajax.call([data])[0];
        if (result) {
            const message = await getString('save_successmessage', 'block_townsquare');
            await Notification.addNotification({message: message, type: 'success'});
        }
    });
}

/**
 * Function to execute existing user settings when loading the townsquare.
 * @param {Object} settingsfromdb
 */
function executeusersettings(settingsfromdb) {

    // First step: set the time filter settings.
    // Change the time into the correct radio button id.
    let futurebuttonid = convertTimeToId(settingsfromdb.timefilterfuture, true);
    let pastbuttonid = convertTimeToId(settingsfromdb.timefilterpast, false);

    // If the time span is a combination of past and future, go through the two radio buttons and activate the filter.
    if (futurebuttonid !== "ts_time_all") {
        alltimebutton.checked = false;
        futureradiobuttons.forEach(function(button) {
            if (button.id === futurebuttonid) {
                button.checked = true;
                button.parentNode.classList.add('active');
                button.dispatchEvent(new Event('change'));
            }
        });
        pastradiobuttons.forEach(function(button) {
            if (button.id === pastbuttonid) {
                button.checked = true;
                button.parentNode.classList.add('active');
                button.dispatchEvent(new Event('change'));
            }
        });
    } else {
        // If the time span is set to all time, activate the all time button.
        alltimebutton.checked = true;
        alltimebutton.parentNode.classList.add('active');
        alltimebutton.dispatchEvent(new Event('change'));
        futureradiobuttons.forEach(button => {
            button.checked = false;
        });
        pastradiobuttons.forEach(button => {
            button.checked = false;
        });
    }

    // Second step: set the letter filter settings.
    // Per default all checkboxes are checked. If the setting is 0, uncheck the checkbox.
    letterCheckboxes.forEach(function(checkbox) {
        let basiclettercheck = checkbox.id === 'basicletter' && settingsfromdb.basicletter === "0";
        let completionlettercheck = checkbox.id === 'completionletter' && settingsfromdb.completionletter === "0";
        let postlettercheck = checkbox.id === 'postletter' && settingsfromdb.postletter === "0";

        if (basiclettercheck || completionlettercheck || postlettercheck) {
            checkbox.click();
        }
    });

    // Third step: set the course filter settings.
    let coursessettings = JSON.parse(settingsfromdb.courses);

    courseCheckboxes.forEach(function(checkbox) {
        let courseid = Number(checkbox.dataset.courseid);
        if (coursessettings.hasOwnProperty(courseid)) {
            // If the setting is false, uncheck the checkbox.
            if (!coursessettings[courseid] && checkbox.checked) {
                checkbox.click();
            }
        }
    });
}

/**
 * Function to collect the letter filter settings.
 * @returns {{basicletter: number, completionletter: number, postletter: number}}
 */
function collectletterfiltersettings() {
    let settings = {'basicletter': 0, 'completionletter': 0, 'postletter': 0};

    letterCheckboxes.forEach(function(checkbox) {
        if (checkbox.checked) {
            switch (checkbox.id) {
                case "basicletter":
                    settings.basicletter = 1;
                    break;
                case "completionletter":
                    settings.completionletter = 1;
                    break;
                case "postletter":
                    settings.postletter = 1;
            }
        }
    });
    // Calculate the setting number. It is a number between 0 and 7, and each letter represents a bit.
    return settings;
}

/**
 * Collects and updates the course filter settings.
 * Ensures previous settings are retained even if a course does not show notifications temporarily..
 *
 * @param {?string} coursesettingsfromdb
 * @returns {string}
 */
function collectcoursesettings(coursesettingsfromdb) {
    // Check if the course settings have been set in the past.
    let settings = coursesettingsfromdb ? JSON.parse(coursesettingsfromdb) : {};

    // Build a JSON in the format courseid => coursename.
    courseCheckboxes.forEach(function(checkbox) {
        settings[Number(checkbox.dataset.courseid)] = checkbox.checked;
    });

    // Return a string version of the settings.
    return JSON.stringify(settings);
}

/**
 * Function to collect the time filter settings.
 * @returns {{timepast: number, timefuture: number}}
 */
function collecttimefiltersettings() {
    let settings = {timepast: 0, timefuture: 0};
    let settingsset = false;

    // Get the relevant time spans of the time filter.
    // Check if the alltimebutton is set.
    if (alltimebutton.checked || alltimebutton.parentNode.classList.contains('active')) {
        settings.timepast = convertIdToTime(alltimebutton.id);
        settings.timefuture = convertIdToTime(alltimebutton.id);
        settingsset = true;
    }

    if (settingsset) {
        return settings;
    }

    // If the alltimebutton is not set, check which of the future/past buttons is set.
    futureradiobuttons.forEach(function(button) {
        if (button.checked || button.parentNode.classList.contains('active')) {
            settings.timefuture = convertIdToTime(button.id);
        }
    });

    pastradiobuttons.forEach(function(button) {
        if (button.checked || button.parentNode.classList.contains('active')) {
            settings.timepast = convertIdToTime(button.id);
        }
    });
    return settings;
}
