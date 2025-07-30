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
 * Javascript for the post letter
 *
 * This file implements following functionality:
 * - cuts posts that have many characters and shows a "see more" Button to see the whole text.
 *
 * @module     block_townsquare/postletter
 * @copyright  2023 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const contentElements = document.getElementsByClassName('postletter_message');
const buttons = document.getElementsByClassName('townsquare_showmore');

const Selectors = {
    actions: {
        seemorebutton: '[data-action="block_townsquare/showmore_button"]',
    },
};

/**
 * Init function
 *
 * The function can cut the text or extract paragraphs of a post.
 */
export function init() {
    // Get the strings for the show more/show less button.
    prefetchStrings('moodle', ['showmore', 'showless',]);

    contentElements.forEach(
        (element) => {
            // Check if the div is too long.
            if (element.clientHeight >= 90) {
                // If the text is too long, show the showmore button.
                buttons[element.id].setAttribute('showmore', 'true');
            } else {
                // If the text is not too long, hide the showmore button.
                buttons[element.id].style.display = "none";
            }
        }
    );

    // Add event listeners for the show more Button.
    addEventListener();
}

/**
 * Event listener for the show more/show less button.
 */
const addEventListener = () => {
    document.addEventListener('click', e => {
        if (e.target.closest(Selectors.actions.seemorebutton)) {
            // Get the id of the clicked element.
            let letterid = e.target.id;
            contentElements.forEach(
                (element) => {
                    if (element.id == letterid) {
                        if (buttons[letterid].getAttribute('showmore') == 'true') {
                            element.classList.add("expanded");
                            changeButtonString(letterid, false);
                        } else {
                            element.classList.remove("expanded");
                            changeButtonString(letterid, true);
                        }
                    }
                }
            );
        }
    });
};

/**
 * Changes the button strings.
 * @param {string} index Which button should be changed
 * @param {boolean} toshowmore a boolean that indicates if the button should show more or less
 */
async function changeButtonString(index, toshowmore) {
    if (toshowmore == true) {
        buttons[index].textContent = await getString('showmore', 'moodle');
        buttons[index].setAttribute('showmore', 'true');
    } else {
        buttons[index].textContent = await getString('showless', 'moodle');
        buttons[index].setAttribute('showmore', 'false');
    }
}
