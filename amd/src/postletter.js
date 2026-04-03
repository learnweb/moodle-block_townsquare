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

import {getString} from "core/str";
import {prefetchStrings} from 'core/prefetch';

/**
 * JavaScript for the post letter
 *
 * @module     block_townsquare/postletter
 * @copyright  2023 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const Selectors = {
    actions: {
        seemorebutton: '[data-action="block_townsquare/showmore_button"]',
    },
};

/**
 * Init function. It limits the height of posts that are too long and adds a "show more" button to them if the user wants to see
 * the full text.
 */
export function init() {
    setup();
    document.addEventListener('click', e => {
        if (e.target.closest(Selectors.actions.seemorebutton)) {
            // Get the id of the clicked element.
            const button = e.target;
            const letterid = Number(button.dataset.parentid);
            const element = document.querySelector(`.postletter_message[data-contentid="${letterid}"]`);
            if (element) {
                // Get the letter group that gets expanded/shrunken. To synchronize both animations, a delta is needed.
                const group = element.closest('.ts-letter-box');
                const delta = element.scrollHeight - 90;

                if (button.getAttribute('showmore') === 'true') {
                    element.style.maxHeight = `${element.scrollHeight}px`;
                    group.style.maxHeight = `${group.scrollHeight + delta}px`;
                    changeButtonString(button, false);
                } else {
                    element.style.maxHeight = '90px';
                    group.style.maxHeight = `${group.scrollHeight - delta}px`;
                    changeButtonString(button, true);
                }
            }
        }
    });
}

/**
 * Setup function.
 */
export function setup() {
    prefetchStrings('moodle', ['showmore', 'showless']);
    document.getElementsByClassName('postletter_message').forEach((element) => {
        const button = document.querySelector(`.townsquare_showmore[data-parentid="${element.dataset.contentid}"]`);
        if (button) {
            if (element.scrollHeight >= 90) {
                button.setAttribute('showmore', 'true');
            } else {
                button.style.display = "none";
            }
        }
    });
}

/**
 * Changes the button strings.
 * @param {HTMLElement} button The button that will be changed
 * @param {boolean} toshowmore a boolean that indicates if the button should show more or less
 */
async function changeButtonString(button, toshowmore) {
    if (toshowmore === true) {
        button.textContent = await getString('showmore', 'moodle');
        button.setAttribute('showmore', 'true');
    } else {
        button.textContent = await getString('showless', 'moodle');
        button.setAttribute('showmore', 'false');
    }
}
