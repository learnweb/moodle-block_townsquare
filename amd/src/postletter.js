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

// Observes clamped messages, as a message that is hidden during the setup can not be measured.
let resizeObserver = null;

/**
 * Init function. Long messages are shortened with a line clamp (data-clamp, see styles.css) and get a "show more" button
 * if the user wants to see the full text.
 */
export function init() {
    setup();
    document.addEventListener('click', e => {
        const button = e.target.closest(Selectors.actions.seemorebutton);
        if (!button) {
            return;
        }
        const letterid = Number(button.dataset.parentid);
        const element = document.querySelector(`.postletter_message[data-contentid="${letterid}"]`);
        if (!element) {
            return;
        }
        const showmore = button.getAttribute('showmore') === 'true';
        toggleClamp(element, showmore);
        changeButtonString(button, !showmore);
    });
}

/**
 * Setup function. Shows the "show more" button only for messages that are too long.
 */
export function setup() {
    prefetchStrings('moodle', ['showmore', 'showless']);
    resizeObserver?.disconnect();
    resizeObserver = new ResizeObserver(entries => entries.forEach(entry => updateClampButton(entry.target)));

    document.querySelectorAll('.postletter_message').forEach((element) => {
        updateClampButton(element);
        resizeObserver.observe(element);
    });
}

/**
 * Shows the "show more" button of a clamped message only if the message is cut.
 * @param {HTMLElement} element The message element
 */
function updateClampButton(element) {
    const button = document.querySelector(`.townsquare_showmore[data-parentid="${element.dataset.contentid}"]`);
    if (!button || element.dataset.clamp !== 'true' || element.clientHeight === 0) {
        // Expanded or hidden messages keep their button state.
        return;
    }
    const isCut = element.scrollHeight > element.clientHeight + 1;
    button.classList.toggle('d-none', !isCut);
    button.setAttribute('showmore', 'true');
}

/**
 * Expands or shortens a message.
 * @param {HTMLElement} element The message element
 * @param {boolean} expand
 */
function toggleClamp(element, expand) {
    element.dataset.clamp = expand ? 'false' : 'true';
    // The letter group needs a new max height, otherwise the expanded message is cut.
    const group = element.closest('.ts-letter-box');
    if (group && group.getAttribute('expanded') !== 'false') {
        group.style.maxHeight = `${group.scrollHeight}px`;
    }
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
