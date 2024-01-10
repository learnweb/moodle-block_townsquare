<?php
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
 * File for the settings of moodleoverflow.
 *
 * @package   blocks_townsquare
 * @copyright 2024 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/blocks/townsquare/lib.php');

    // Color Setting for basic letters.
    $basicletteroptions = [];
    $basicletteroptions[TOWNSQUARE_PRIMARY_COLOR] = get_string('primarycolor', 'block_townsquare');
    $basicletteroptions[TOWNSQUARE_BLUE_COLOR] = get_string('bluecolor', 'block_townsquare');
    $settings->add(new admin_setting_configselect('block_townsquare/basiclettercolor',
                                                  get_string('basiclettercolor', 'block_townsquare'),
                                                  get_string('configbasiclettercolor', 'block_townsquare'),
                                 TOWNSQUARE_PRIMARY_COLOR, $basicletteroptions));

    // Color Setting for the orientation marker.
    $orientationmarkeroptions = [];
    $orientationmarkeroptions[TOWNSQUARE_SECONDARY_COLOR] = get_string('secondarycolor', 'block_townsquare');
    $orientationmarkeroptions[TOWNSQUARE_GRAY_COLOR] = get_string('graycolor', 'block_townsquare');
    $settings->add(new admin_setting_configselect('block_townsquare/orientationmarkercolor',
                                                  get_string('orientationmarkercolor', 'block_townsquare'),
                                                  get_string('configorientationmarkercolor', 'block_townsquare'),
                                 TOWNSQUARE_SECONDARY_COLOR, $orientationmarkeroptions));

}
