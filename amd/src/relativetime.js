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
 * JavaScript for the relative time of letters.
 *
 * Letters that are less than a day away from now show a relative time, e.g. "in 2 hours" or "3 hours ago".
 * The text is formatted by the browser in the language of the page and is updated every minute.
 *
 * @module     block_townsquare/relativetime
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const SELECTOR = '[data-region="ts_relativetime"]';
const HOUR = 3600;
const DAY = 86400;

let interval = null;
let formatter = null;

/**
 * Init function.
 */
export function init() {
    update();
    if (interval === null) {
        interval = setInterval(update, 60000);
    }
}

/**
 * Updates the relative time of all letters. Is also used after the content was reloaded.
 */
export function update() {
    const now = Date.now() / 1000;
    document.querySelectorAll(SELECTOR).forEach(element => {
        const diff = Number(element.dataset.timestamp) - now;
        if (Math.abs(diff) >= DAY) {
            element.textContent = '';
            element.classList.add('d-none');
            return;
        }
        element.textContent = Math.abs(diff) < HOUR
            ? getFormatter().format(Math.round(diff / 60), 'minute')
            : getFormatter().format(Math.round(diff / HOUR), 'hour');
        element.classList.remove('d-none');
    });
}

/**
 * Returns a formatter for the language of the page.
 * @returns {Intl.RelativeTimeFormat}
 */
function getFormatter() {
    if (formatter === null) {
        try {
            formatter = new Intl.RelativeTimeFormat(document.documentElement.lang || undefined, {numeric: 'auto'});
        } catch (e) {
            // Unknown language tag.
            formatter = new Intl.RelativeTimeFormat(undefined, {numeric: 'auto'});
        }
    }
    return formatter;
}
