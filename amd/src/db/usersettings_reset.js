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
 * JavaScript to reset the user settings.
 *
 * This file implements 1 functionality:
 * - If the "reset settings" button is pressed, reset all settings and delete the users database record.
 *
 * @module     block_townsquare/db/usersettings_reset
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getString} from "core/str";
import Notification from 'core/notification';

const resetbutton = document.getElementById('ts_usersettings_resetbutton');

/**
 * Init function. Resets user settings from the database.
 * @param {number} userid The id of the current user.
 */
export function init(userid) {
    resetbutton.addEventListener('click', async() => {
        const data = {methodname: 'block_townsquare_reset_usersettings', args: {userid}};
        const result = await Ajax.call([data])[0];

        if (result) {
            const message = await getString('reset_successmessage', 'block_townsquare');
            Notification.addNotification({message, type: 'success'});
        }

        document.querySelectorAll('.ts_course_checkbox:not(:checked)').forEach(checkbox => checkbox.click());
        document.querySelectorAll('.ts_all_time_button').forEach(button => {
            button.checked = true;
            button.parentNode.classList.add('active');
            button.dispatchEvent(new Event('change'));
        });
        document.querySelectorAll('.ts_letter_checkbox:not(:checked)').forEach(checkbox => checkbox.click());

        return result;
    });
}
