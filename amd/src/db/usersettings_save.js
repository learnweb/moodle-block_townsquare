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

// Get the save button for the user settings.
const savebutton = document.getElementById('ts_usersettings_savebutton');

// The radio buttons of the time filter.
const timeButtonSelector = '.ts_time_button';

// The checkboxes of the course filter are not kept in a constant, because a reload replaces them.
const courseCheckboxSelector = '.ts_course_checkbox';

/**
 * Init function. This functions adapts the filter settings to the user settings from the database. If the user changes settings
 * and clicks the save button, the settings are stored in the database.
 *
 * @param {object} settingsfromdb   The settings from the database, if there are any.
 */
export function init(settingsfromdb) {
    // When the page is loaded, set the settings from the database.
    if (settingsfromdb) {
        executeusersettings(settingsfromdb);
    }

    // Add event listener to the save button.
    savebutton.addEventListener('click', async function() {

        // First step: collect the current settings.
        // Get the relevant time spans of the time filter.
        let timespans = collecttimefiltersettings();
        let courses = collectcoursesettings(settingsfromdb.courses);

        const data = {
            methodname: 'block_townsquare_record_usersettings',
            args: {
                timefilterpast: timespans.timepast,
                timefilterfuture: timespans.timefuture,
                courses: courses,
            },
        };

        try {
            await Ajax.call([data])[0];
        } catch (error) {
            Notification.exception(error);
            return;
        }
        const message = await getString('save_successmessage', 'block_townsquare');
        await Notification.addNotification({message: message, type: 'success'});

    });
}

/**
 * Function to execute existing user settings when loading the townsquare.
 * @param {Object} settingsfromdb
 */
function executeusersettings(settingsfromdb) {

    // First step: set the time filter. Older settings can have different time spans for the past and the future,
    // then the longer one is used.
    const timespan = Math.max(Number(settingsfromdb.timefilterpast), Number(settingsfromdb.timefilterfuture));
    const timebutton = [...document.querySelectorAll(timeButtonSelector)]
        .find(button => Number(button.dataset.timespan) === timespan) ?? document.getElementById('ts_time_all');
    timebutton.checked = true;
    timebutton.dispatchEvent(new Event('change'));

    // Second step: set the course filter settings.
    let coursessettings = JSON.parse(settingsfromdb.courses);

    document.querySelectorAll(courseCheckboxSelector).forEach(function(checkbox) {
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
    document.querySelectorAll(courseCheckboxSelector).forEach(function(checkbox) {
        settings[Number(checkbox.dataset.courseid)] = checkbox.checked;
    });

    // Return a string version of the settings.
    return JSON.stringify(settings);
}

/**
 * Function to collect the time filter settings. The time span is the same for the past and the future.
 * @returns {{timepast: number, timefuture: number}}
 */
function collecttimefiltersettings() {
    const timespan = Number(document.querySelector(`${timeButtonSelector}:checked`).dataset.timespan);
    return {timepast: timespan, timefuture: timespan};
}
