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
 * JavaScript for the filter bar.
 *
 * This file implements 2 functionalities:
 * - Shows the selected courses in the course filter button, e.g. "All courses" or "2 of 3 courses".
 * - Shows the selected time span in the time filter button, e.g. "All time" or "±1 week".
 *   A filter that does not show everything is highlighted.
 *
 * @module     block_townsquare/filter/filterbar
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const Selectors = {
    root: '#ts_filter',
    courseCheckbox: '.ts_course_checkbox',
    courseLabel: '[data-region="ts_course_label"]',
    timeLabel: '[data-region="ts_time_label"]',
    selectedTime: '.ts_time_button:checked',
};

/**
 * Updates the course count and the time summary based on the current state of the filter inputs.
 * Is called after every filter change (see refresh() in filtercontroller).
 */
export function update() {
    const root = document.querySelector(Selectors.root);
    updateCourseLabel(root);
    updateTimeLabel(root);
}

/**
 * Shows the selected courses in the course filter button.
 * @param {HTMLElement} root
 */
function updateCourseLabel(root) {
    const label = root.querySelector(Selectors.courseLabel);
    const checkboxes = [...root.querySelectorAll(Selectors.courseCheckbox)];
    const selected = checkboxes.filter(checkbox => checkbox.checked).length;
    const all = selected === checkboxes.length;
    label.textContent = all ? label.dataset.all
        : label.dataset.some.replace('{selected}', selected).replace('{total}', checkboxes.length);
    setFilterActive(label.closest('button'), !all);
}

/**
 * Shows the selected time span in the time filter button, e.g. "All time" or "±1 week".
 * @param {HTMLElement} root
 */
function updateTimeLabel(root) {
    const label = root.querySelector(Selectors.timeLabel);
    const selected = root.querySelector(Selectors.selectedTime);
    const all = selected.id === 'ts_time_all';
    label.textContent = all ? label.dataset.all : `\u00b1${selected.labels[0].textContent.trim()}`;
    setFilterActive(label.closest('button'), !all);
}

/**
 * Highlights the button of a filter that does not show everything.
 * @param {HTMLElement} button
 * @param {boolean} active
 */
function setFilterActive(button, active) {
    button.classList.toggle('text-primary', active);
    button.classList.toggle('text-body-secondary', !active);
}
