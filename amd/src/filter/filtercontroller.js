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
 * JavaScript to show/hide letters based on all filters.
 *
 * Every filter marks the letters it approves with a class and calls refresh() afterwards. refresh() shows the letters
 * that are approved by all filters and updates everything that depends on the visible letters or the filter settings.
 *
 * @module     block_townsquare/filter/filtercontroller
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {update as updateFilterBar} from 'block_townsquare/filter/filterbar';

const Selectors = {
    letter: '.townsquare_letter',
    group: '.ts-lettergroup',
    groupLetters: '.ts-letter-box > *',
    dayCount: '[data-region="ts_day_count"]',
    activeTab: '.ts_letter_tabs .nav-link[aria-pressed="true"]',
    emptyMessage: '.ts_empty',
    emptyHint: '[data-region="ts_empty_hint"]',
    emptyFiltered: '[data-region="ts_empty_filtered"]',
    showAll: '[data-action="block_townsquare/show_all"]',
    courseCheckbox: '.ts_course_checkbox',
    allTime: '#ts_time_all',
};

/**
 * Init function. Controls the visibility of letters based on the approval of all filters.
 */
export function init() {
    setup();
    refresh();
    document.addEventListener('click', e => {
        if (e.target.closest(Selectors.showAll)) {
            showAll();
        }
    });
}

/**
 * Activates every letter by adding the filter classes.
 * Is also used after the content was reloaded, as the reload replaces all letters.
 */
export function setup() {
    document.querySelectorAll(Selectors.letter).forEach(function(letter) {
        letter.classList.add('ts_coursefilter_approved', 'ts_timefilter_approved', 'ts_letterfilter_approved');
    });
}

/**
 * Shows the letters that are approved by all filters and hides the others. Is called by every filter after it was applied.
 */
export function refresh() {
    document.querySelectorAll(Selectors.letter).forEach(function(letter) {
        letter.style.display = isApproved(letter) ? 'block' : 'none';
    });
    document.querySelectorAll(Selectors.group).forEach(updateLetterGroup);
    updateEmptyMessage();
    updateFilterBar();
}

/**
 * Checks if a letter is approved by all filters.
 * @param {HTMLElement} letter
 * @returns {boolean}
 */
function isApproved(letter) {
    return letter.classList.contains('ts_coursefilter_approved') &&
        letter.classList.contains('ts_timefilter_approved') &&
        letter.classList.contains('ts_letterfilter_approved');
}

/**
 * Shows a letter group only if at least one of its letters is visible.
 * @param {HTMLElement} group
 */
function updateLetterGroup(group) {
    const anyVisible = [...group.querySelectorAll(Selectors.groupLetters)].some(isApproved);
    group.style.display = anyVisible ? 'block' : 'none';
}

/**
 * Shows the message of the selected tab if it has no letters to show, so that an empty tab looks intended.
 * If the course or time filter is set, the message offers to show all letters.
 */
function updateEmptyMessage() {
    const anyVisible = [...document.querySelectorAll(Selectors.letter)].some(isApproved);
    const activeTab = document.querySelector(Selectors.activeTab)?.id;
    const filtered = [...document.querySelectorAll(Selectors.courseCheckbox)].some(checkbox => !checkbox.checked) ||
        !document.querySelector(Selectors.allTime).checked;
    document.querySelectorAll(Selectors.emptyMessage).forEach(message => {
        message.classList.toggle('d-none', anyVisible || message.dataset.tab !== activeTab);
        message.querySelector(Selectors.emptyHint).classList.toggle('d-none', filtered);
        message.querySelector(Selectors.emptyFiltered).classList.toggle('d-none', !filtered);
    });
}

/**
 * Selects all courses and all time in the filters (not saved). The filters react to the change events.
 */
function showAll() {
    document.querySelectorAll(`${Selectors.courseCheckbox}:not(:checked)`).forEach(checkbox => checkbox.click());
    const allTime = document.querySelector(Selectors.allTime);
    if (!allTime.checked) {
        allTime.checked = true;
        allTime.dispatchEvent(new Event('change'));
    }
}

