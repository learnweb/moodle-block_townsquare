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
 * JavaScript for the letter filter
 *
 * The tabs "Posts" and "Deadlines" show the letters of the types in their data-letters attribute
 * (postletter, basicletter, completionletter). The selected tab is not saved, the block always starts with "Posts".
 *
 * @module     block_townsquare/filter/letterfilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {refresh} from 'block_townsquare/filter/filtercontroller';

const Selectors = {
    tab: '.ts_letter_tabs .nav-link',
    activeTab: '.ts_letter_tabs .nav-link[aria-pressed="true"]',
    letter: '.townsquare_letter',
    letters: '.townsquare_letters',
};

/**
 * Init function. Selects a tab when it is clicked.
 */
export function init() {
    document.querySelectorAll(Selectors.tab).forEach(tab => tab.addEventListener('click', () => selectTab(tab)));
    apply();
}

/**
 * Marks the clicked tab as selected and shows its letters from the top of the list.
 * @param {HTMLElement} selectedTab
 */
function selectTab(selectedTab) {
    document.querySelectorAll(Selectors.tab).forEach(tab => {
        tab.classList.toggle('active', tab === selectedTab);
        tab.setAttribute('aria-pressed', tab === selectedTab ? 'true' : 'false');
    });
    apply();
    document.querySelector(Selectors.letters).scrollTop = 0;
}

/**
 * Applies the letter filter to all letters: the letters of the types of the selected tab are "approved".
 * Is also used after the content was reloaded.
 */
export function apply() {
    const types = document.querySelector(Selectors.activeTab).dataset.letters.split(' ');
    document.querySelectorAll(Selectors.letter).forEach(letter => {
        letter.classList.toggle('ts_letterfilter_approved', types.some(type => letter.classList.contains(type)));
    });
    refresh();
}
