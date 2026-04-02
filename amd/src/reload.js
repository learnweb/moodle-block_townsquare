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
 * JavaScript for the reload button.
 *
 * @module     block_townsquare/reload
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {setup as lettergroupSetup} from 'block_townsquare/lettergroup';
import {setup as postletterSetup} from 'block_townsquare/postletter';

/**
 * Init function.
 */
export function init() {
    document.addEventListener('click', async function(e) {
        if (e.target.closest('#ts_reload_button')) {
            document.querySelector('.townsquare_content').innerHTML = await Ajax.call([{
                methodname: "block_townsquare_reload",
                args: {},
            }])[0];

            lettergroupSetup();
            postletterSetup();
        }
    });
}