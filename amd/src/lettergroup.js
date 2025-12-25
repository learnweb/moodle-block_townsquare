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
 * JavaScript for the letter group.
 *
 * @module     block_townsquare/lettergroup
 * @copyright  2025 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const letterboxes = document.getElementsByClassName('ts-letter-box');

const Selectors = {
    actions: {
        collapsegroup: '[data-action="block_townsquare/collapse_group"]',
    },
};

/**
 * Init function. Adds event listener to orientation marker of letter group to enable
 * collapsing a letter group.
 */
export function init() {
    letterboxes.forEach(
        (element) => {
            element.style.maxHeight = `${element.scrollHeight}px`;
            element.setAttribute('expanded', 'true');
        }
    );

    document.addEventListener('click', e => {
        const group = e.target.closest(Selectors.actions.collapsegroup);
        if (group) {
            const icon = group.querySelector('i');
            let groupid = group.dataset.groupid;
            letterboxes.forEach(
                (element) => {
                    if (element.dataset.groupid === groupid) {
                        if (element.getAttribute('expanded') === 'true') {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-up');
                            element.style.maxHeight = '0px';
                            element.setAttribute('expanded', 'false');
                        } else {
                            icon.classList.remove('fa-chevron-up');
                            icon.classList.add('fa-chevron-down');
                            element.style.maxHeight = `${element.scrollHeight}px`;
                            element.setAttribute('expanded', 'true');
                        }
                    }
                }
            );
        }
    });
}