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
import Pending from 'core/pending';
import {setup as postletterSetup} from 'block_townsquare/postletter';
import {setup as filtercontrollerSetup} from 'block_townsquare/filter/filtercontroller';
import {apply as applyCourseFilter, updateCourseList} from 'block_townsquare/filter/coursefilter';
import {apply as applyLetterFilter} from 'block_townsquare/filter/letterfilter';
import {apply as applyTimeFilter} from 'block_townsquare/filter/timefilter';
import {update as updateRelativeTime} from 'block_townsquare/relativetime';

let isReloading = false;

/**
 * Init function. Reloads the content when the reload button is clicked.
 *
 * While loading, the reload icon spins and the content is dimmed. Afterwards, the scroll position, the collapsed
 * letter groups and the filters are kept.
 */
export function init() {
    document.addEventListener('click', async function(e) {
        const button = e.target.closest('#ts_reload_button');
        if (!button || isReloading) {
            return;
        }
        isReloading = true;
        const pending = new Pending('block_townsquare/reload');

        const content = document.querySelector('.townsquare_content');
        const scrollPosition = content.querySelector('.townsquare_letters').scrollTop;
        const collapsedGroups = [...content.querySelectorAll('.ts-letter-box:not(.show)')].map(box => box.dataset.groupid);
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        button.querySelector('i').classList.add('fa-spin');
        content.classList.add('opacity-50');
        content.setAttribute('aria-busy', 'true');
        try {
            // Wait at least one rotation of the reload icon, so that a fast reload does not flicker.
            const [result] = await Promise.all([
                Ajax.call([{methodname: "block_townsquare_reload", args: {}}])[0],
                new Promise(resolve => setTimeout(resolve, reduceMotion ? 0 : 600)),
            ]);

            content.innerHTML = result;

            // The reload button is outside of the reloaded content and shows the last update.
            const letters = content.querySelector('.townsquare_letters');
            button.title = letters.dataset.reloadlabel;
            button.setAttribute('aria-label', letters.dataset.reloadlabel);

            // The course filter shows the courses of the new letters.
            updateCourseList();

            // The new letters do not know the current filter settings yet.
            filtercontrollerSetup();
            applyCourseFilter();
            applyLetterFilter();
            applyTimeFilter();

            collapseGroups(collapsedGroups);
            postletterSetup();
            updateRelativeTime();

            // Stay at the same position in the list.
            letters.scrollTop = scrollPosition;
        } catch (error) {
            Notification.exception(error);
        } finally {
            content.classList.remove('opacity-50');
            content.removeAttribute('aria-busy');
            button.querySelector('i').classList.remove('fa-spin');
            isReloading = false;
            pending.resolve();
        }
    });
}

/**
 * Collapses the letter groups again that were collapsed before the reload. Bootstrap shows a group if it has the class
 * "show" and marks its button with the class "collapsed" (see the day header in content.mustache).
 * @param {string[]} groupIds
 */
function collapseGroups(groupIds) {
    groupIds.forEach(id => {
        document.getElementById(`ts-letterbox-${id}`)?.classList.remove('show');
        const button = document.querySelector(`[data-bs-target="#ts-letterbox-${id}"]`);
        button?.classList.add('collapsed');
        button?.setAttribute('aria-expanded', 'false');
    });
}
