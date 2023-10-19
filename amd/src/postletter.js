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
//import Prefetch from "../../../../lib/amd/src/prefetch";

/**
 * Javascript for the post letter
 *
 * @module     block_townsquare/postletter
 * @copyright  2023 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const contentElements = document.getElementsByClassName('postletter_message');
const buttons = document.getElementsByClassName('townsquare_showmore');
const originalTexts = [];

const Selectors = {
    actions: {
        seemorebutton: '[data-action="block_townsquare/showmore_button"]',
    },
};

// TODO: The <p> parameter gets lost with this method. Search for a better solution.
// TODO: Don't cut within a word or after a space.
/**
 * Init function
 *
 * The function should cut the text if it is too long
 */
export function init() {
    // Go through all elements and check if the text is too long.
    contentElements.forEach(
        (element) => {
            if (element.textContent.length >= 250) {
                // If the text is too long, cut it.
                originalTexts[element.id] = element.textContent;
                element.textContent = element.textContent.substring(0,250) + "...";
                buttons[element.id].setAttribute('showmore', 'true');
            } else {
                // If the text is not too long, hide the show more button.
                buttons[element.id].style.display = "none";
            }
        }
    );
    registerEventListener();
}

/**
 * Event listener for the show more/show less button.
 */
const registerEventListener = () => {
    document.addEventListener('click', e => {
        if (e.target.closest(Selectors.actions.seemorebutton)) {
            // Get the id of the clicked element.
            let letterid = e.target.id;
            contentElements.forEach(
                (element) => {
                    if (element.id == letterid) {
                        if (buttons[letterid].getAttribute('showmore')) {
                            element.textContent = originalTexts[letterid];
                            buttons[letterid].textContent = getString('showless', 'block_townsquare');
                            buttons[letterid].setAttribute('showmore', 'false');
                        } else {
                            element.textContent = element.textContent.substring(0,250) + "...";
                            buttons[letterid].textContent = getString('showmore', 'block_townsquare');
                            buttons[letterid].setAttribute('showmore', 'true');
                        }
                    }
                }
            );
        }
    });
};
