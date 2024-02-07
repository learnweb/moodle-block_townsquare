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
 * Javascript for the time filter
 *
 * This file implements 1 functionality:
 * - Checks, which of the radio buttons is pressed and filters the content based on the time.
 *
 * @module     block_townsquare/timefilter
 * @copyright  2024 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get the relevant radio buttons.
const radiobuttons = document.querySelectorAll('.ts_time_button');

/**
 * Init function
 */
export function init() {
    radiobuttons.forEach(function(radiobutton) {
        radiobutton.addEventListener('change', function() {
            // Get the current time in seconds.
            let currenttime = new Date().getTime() / 1000;
            let timestart;
            let timeend;

            // Depending on the radiobutton id, set a time span.
            switch (radiobutton.id) {
                case "ts_time_all":
                    timestart = -9999999999;
                    timeend = 9999999999;
                    break;
                case "ts_time_next_week":
                    timestart = currenttime;
                    timeend = currenttime + 604800;
                    break;
                case "ts_time_next_month":
                    timestart = currenttime;
                    timeend = currenttime + 2629743;
                    break;
                case "ts_time_last_week":
                    timestart = currenttime - 604800;
                    timeend = currenttime;
                    break;
                case "ts_time_last_month":
                    timestart = currenttime - 2629743;
                    timeend = currenttime;
                    break;
                default:
                    timestart = -9999999999;
                    timeend = 9999999999;
                    break;
            }

            // Get all the right letters.
            const letters = document.querySelectorAll('.townsquare_letter');

            // Loop through each letter and hide/show based on radiobutton state.
            letters.forEach(function(letter) {
                // Get the created time stamp of each letter.
                let lettertime = letter.querySelector('.townsquareletter_date').id;

                // If the radio button is checked and the letter is in the time span, show it.
                if (radiobutton.checked) {
                    if (lettertime >= timestart && lettertime <= timeend) {
                        letter.style.display = 'block'; // Show the letter.
                    } else {
                        letter.style.display = 'none'; // Hide the letter.
                    }
                } else {
                    letter.style.display = 'block'; // Show the letter.
                }

            });
        });
    });
}
