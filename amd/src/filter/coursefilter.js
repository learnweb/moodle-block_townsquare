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
 * JavaScript for the course filter
 *
 * This file implements 2 functionalities:
 * - Checks the checkboxes of the course filter and hides content from courses if the checkbox is not checked.
 * - Updates the course list after a reload, so that it shows the courses of the new content.
 *
 * @module     block_townsquare/filter/coursefilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {refresh} from 'block_townsquare/filter/filtercontroller';

const Selectors = {
    menu: '.ts_course_menu',
    checkbox: '.ts_course_checkbox',
    newCourseList: '[data-region="ts_course_list"]',
};

/**
 * Init function. Adds an event listener to the course filter checkboxes. This is part of the content filtering functionality.
 * Every checkbox represents one course that shows content in Town square.
 */
export function init() {
    // One listener on the menu, so that it also works for the checkboxes of a reload.
    document.querySelector(Selectors.menu).addEventListener('change', apply);
}

/**
 * Replaces the course list with the courses of the reloaded content. Courses that were in the list before keep
 * their state, new courses are checked.
 */
export function updateCourseList() {
    const menu = document.querySelector(Selectors.menu);
    const checkedStates = new Map([...menu.querySelectorAll(Selectors.checkbox)]
        .map(checkbox => [checkbox.dataset.courseid, checkbox.checked]));

    menu.replaceChildren(document.querySelector(Selectors.newCourseList).content);
    menu.querySelectorAll(Selectors.checkbox).forEach(function(checkbox) {
        if (checkedStates.has(checkbox.dataset.courseid)) {
            checkbox.checked = checkedStates.get(checkbox.dataset.courseid);
        }
    });
}

/**
 * Applies the course filter to all letters. Letters from courses whose checkbox is not checked are not "approved".
 * Is also used after the content was reloaded.
 */
export function apply() {
    // Collect the ids of all courses that are selected.
    const approvedcourses = [...document.querySelectorAll(Selectors.checkbox)]
        .filter(checkbox => checkbox.checked)
        .map(checkbox => Number(checkbox.dataset.courseid));

    // Loop through each letter and mark it as "approved" or not based on its course.
    document.querySelectorAll('.townsquare_letter').forEach(function(letter) {
        const lettercourseid = Number(letter.querySelector('.townsquareletter_course').dataset.courseid);
        letter.classList.toggle('ts_coursefilter_approved', approvedcourses.includes(lettercourseid));
    });
    refresh();
}
