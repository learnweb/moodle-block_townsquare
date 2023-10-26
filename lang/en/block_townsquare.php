<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     block_townsquare
 * @category    string
 * @copyright   2023 Tamaro Walter
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Core strings for the installation.
$string['pluginname'] = 'Townsquare block';
$string['pluginname'] = 'Town Square';
$string['pluginname:addinstance'] = 'Add a new Town Square block';
$string['pluginname:myaddinstance'] = 'Add a new Town Square block to the My Moodle page';

// Mustache strings.
$string['lettercourse'] = 'Course: {$a->course}';
$string['completionletterorigin'] = 'A completion is required by {$a->modulename}:';
$string['postletternotification'] = 'New {$a->modulename} post!';
$string['postletterorigin'] = '{$a->authorname} posted in {$a->localname} -> {$a->discussionname}';
$string['showmore'] = 'Show more';
$string['showless'] = 'Show less';
$string['orientationmarkercontent'] = 'Hi {$a->username}, welcome to townsquare! Today is the {$a->date}';

// Letter strings.
$string['invalidmodulename'] = 'Module name is unknown or not supported';

// Letter Controller strings.
$string['eventsempty'] = 'Events need to be retrieved before working with them';

// Test strings.
$string['seitenpanel'] = 'In development...';
$string['hauptseite'] = 'Ich bin die hauptseite';
