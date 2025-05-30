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
 * @package   block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
global $ADMIN, $CFG;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/blocks/townsquare/lib.php');

    // Time setting for the search span.
    $options = [];
    $options[TOWNSQUARE_TIME_TWOMONTHS] = get_string('timetwomonths', 'block_townsquare');
    $options[TOWNSQUARE_TIME_THREEMONTHS] = get_string('timethreemonths', 'block_townsquare');
    $options[TOWNSQUARE_TIME_SIXMONTHS] = get_string('timesixmonths', 'block_townsquare');

    $settings->add(new admin_setting_configselect('block_townsquare/timespan', get_string('timespan', 'block_townsquare'),
        get_string('configtimespan', 'block_townsquare'), TOWNSQUARE_TIME_THREEMONTHS, $options));

    // Color setting for the color that will be used on basic letters.
    $settings->add(new admin_setting_configcolourpicker('block_townsquare/basiclettercolor',
                                                        get_string('basiclettercolor', 'block_townsquare'),
                                                        get_string('configbasiclettercolor', 'block_townsquare'),
                                        TOWNSQUARE_BASICLETTER_DEFAULTCOLOR));

    // Color setting for the color that will be used on post letters.
    $settings->add(new admin_setting_configcolourpicker('block_townsquare/postlettercolor',
                                                        get_string('postlettercolor', 'block_townsquare'),
                                                        get_string('configpostlettercolor', 'block_townsquare'),
                                        TOWNSQUARE_POSTLETTER_DEFAULTCOLOR));

    // Color setting for the color that will be used on completion letters.
    $settings->add(new admin_setting_configcolourpicker('block_townsquare/completionlettercolor',
                                                        get_string('completionlettercolor', 'block_townsquare'),
                                                        get_string('configcompletionlettercolor', 'block_townsquare'),
                                        TOWNSQUARE_COMPLETIONLETTER_DEFAULTCOLOR));

    // Color setting for the orientation marker.
    $settings->add(new admin_setting_configcolourpicker('block_townsquare/orientationmarkercolor',
                                                        get_string('orientationmarkercolor', 'block_townsquare'),
                                                        get_string('configorientationmarkercolor', 'block_townsquare'),
                                        TOWNSQUARE_ORIENTATIONMARKER_DEFAULTCOLOR));
}
