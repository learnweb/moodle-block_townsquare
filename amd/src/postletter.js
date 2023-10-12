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
 * Javascript for the post letter
 *
 * @module     block_townsquare/postletter
 * @copyright  2023 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//import Ajax from 'core/ajax';
//import Prefetch from 'core/prefetch';
//import {get_string as getString} from 'core/str';

// Define const.

//const root = document.getElementById('townsquare_root');
//const Selectors = {}

/**
 * Init function
 */
export function init() {

    // The function should cut the text if it is too long.

    // Step 1: count the characters or words or lines in the div.
    // Step 2: Cut the text if necessary
    // Step 3: Save the hidden text part
    // Step 4: If the button "show more" is pressed, the hidden text will be added and the box will be printed bigger

    var posts = document.getElementsByClassName('postletter_content');

    for (let i= 0; i < posts.length; i++) {
        let text = posts[i].textContent;
        let numberOfchar = text.length;
        posts[i].innerHTML = text +  ' number of Chars: '  + numberOfchar;
    }
    //let numberofChar = posts[0].textContent.length;
    //posts[0].innerHTML += ' number of chars: ' + numberofChar;
}



