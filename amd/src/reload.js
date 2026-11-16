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
 * JavaScript for the reload button.
 *
 * @module     block_townsquare/reload
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import {setup as lettergroupSetup} from 'block_townsquare/lettergroup';
import {setup as postletterSetup} from 'block_townsquare/postletter';
import {setup as filtercontrollerSetup} from 'block_townsquare/filter/filtercontroller';
import {apply as applyCourseFilter, updateCourseList} from 'block_townsquare/filter/coursefilter';
import {apply as applyLetterFilter} from 'block_townsquare/filter/letterfilter';
import {apply as applyTimeFilter} from 'block_townsquare/filter/timefilter';

let isReloading = false;

/**
 * Init function.
 */
export function init() {
    document.addEventListener('click', async function(e) {
        const button = e.target.closest('#ts_reload_button');
        if (button && !isReloading) {
            isReloading = true;
            // Save all closed letter groups to enable to expand them afterwards.
            const groupSelector = '.ts-letter-box[expanded="false"]';
            const closedLetterGroups = [...document.querySelectorAll(groupSelector)].map(el => el.dataset.groupid);

            // Add animation to the reload icon. Then call the new build and wait at least 600ms for the animation.
            button.classList.add('ts-reloading');
            try {
                const [result] = await Promise.all([
                    Ajax.call([{methodname: "block_townsquare_reload", args: {}}])[0],
                    new Promise(resolve => setTimeout(resolve, 600)),
                ]);

                document.querySelector('.townsquare_content').innerHTML = result;

                // The reload button is outside of the reloaded content and shows the last update.
                const reloadlabel = document.querySelector('.townsquare_letters').dataset.reloadlabel;
                button.title = reloadlabel;
                button.setAttribute('aria-label', reloadlabel);

                // The course filter shows the courses of the new letters.
                updateCourseList();

                // The new letters do not know the current filter settings yet.
                filtercontrollerSetup();
                applyCourseFilter();
                applyLetterFilter();
                applyTimeFilter();

                lettergroupSetup(closedLetterGroups);
                postletterSetup();
            } catch (error) {
                Notification.exception(error);
            } finally {
                button.classList.remove('ts-reloading');
                isReloading = false;
            }
        }
    });
}
