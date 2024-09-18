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
 * Javascript to reset the user settings..
 *
 * This file implements 1 functionality:
 * - If the "reset settings" button is pressed, reset all settings and delete the users database record.
 *
 * @module     block_townsquare/usersettings_reset
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

// Get the reset button for the user settings.
const resetbutton = document.getElementById('ts_usersettings_resetbutton');

/**
 * Init function
 * @param {number} userid The id of the current user.
 */
export function init(userid) {
    // First step: delete user settings in database.

    // Add event listener to the reset button.
    resetbutton.addEventListener('click', async function() {
        // Set up for AJAX call.
        const data = {
            methodname: 'block_townsquare_reset_usersettings',
            args: {
                userid: userid,
            },
        };
        // Call the AJAX function.
        let result = Ajax.call([data]);

        // Make the clicked button green by adding a class and remove it afterward.
        resetbutton.classList.add('bg-success', 'text-white', 'ts_button_transition');
        setTimeout(function() {
            resetbutton.classList.remove('bg-success');
            resetbutton.classList.remove('text-white');
        }, 1500);

        // Second step: reset all active filters.
        const coursecheckboxes = document.querySelectorAll('.ts_course_checkbox');
        const lettercheckboxes = document.querySelectorAll('.ts_letter_checkbox');
        const alltimebutton = document.querySelectorAll('.ts_all_time_button');

        coursecheckboxes.forEach(function(checkbox) {
            if (!checkbox.checked) {
                checkbox.click();
            }
        });

        alltimebutton.forEach(function(button) {
            button.parentNode.classList.add('active');
            button.checked = true;
            button.dispatchEvent(new Event('change'));
        });

        lettercheckboxes.forEach(function(checkbox) {
            if (!checkbox.checked) {
                checkbox.click();
            }
        });
        return result;
    });
}
