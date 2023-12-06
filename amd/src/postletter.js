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
 * This file implements 2 functionalities:
 * - cuts posts that have many characters and shows a "see more" Button to see the whole text.
 * - Unnecessary <p> Tags from the Database are being replaced with line breaks to make the text more readable.
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

/**
 * Init function
 *
 * The function can cut the text or extract paragraphs of a post.
 */
export function init() {
    contentElements.forEach(
        (element) => {
            // Replace all <p> within the text with simple line breaks..
            replaceParagraghTags(element);

            // Check if the text is too long.
            if (element.textContent.length >= 250) {
                // If the text is too long, cut it.
                originalTexts[element.id] = element.innerHTML;
                cutString(element);
                element.parentElement.insertAdjacentHTML('beforeend', '<p>');
                buttons[element.id].setAttribute('showmore', 'true');
            } else {
                // If the text is not too long, hide the show more button.
                buttons[element.id].style.display = "none";
            }
        }
    );

    // Get the strings for the show more/show less button.
    prefetchStrings('moodle', ['showmore', 'showless',]);

    // Add event listeners for the show more Button.
    addEventListener();
}

/**
 * Function to cut a String at a length of 250 characters.
 * The function does not cut within a word or after a space.
 * If the cutting point is within a word, the function searches for the next space and cuts there.
 * @param {object} element
 */
function cutString(element) {
    let text = element.textContent;
    let index = 250;
    while (text.charAt(index) != " ") {
        index++;
    }
    element.innerHTML = text.substring(0,index);
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
                            element.innerHTML = originalTexts[letterid];
                            changeButtonString(letterid, false);
                        } else {
                            cutString(element);
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

/**
 * Removes in a text all &nbsp; and surrounding <p> tags excluding the first occurrence.
 *
 * Helper function to make post look better.
 * @param {object} element
 */
async function replaceParagraghTags(element) {
    // Identify and store the first <p> and </p> tags
    let message = element.innerHTML;
    const firstPTag = message.indexOf('<p>');
    const lastPTag = message.lastIndexOf('</p>');

    // Remove &nbsp; and surrounding <p> tags excluding the first occurrence
    message = message.replace(/<p>&nbsp;<\/p>/g, '').replace(/&nbsp;/g, '');

    // Replace <p> tags with <br> excluding the first occurrence
    message = message.substring(0, firstPTag + 3) +
        message.substring(firstPTag + 3, lastPTag).replace(/<p>/g, '<br>').replace(/<\/p>/g, '') +
        message.substring(lastPTag);
    element.innerHTML = message;
}