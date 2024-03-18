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
$string['pluginname:addinstance'] = 'Add the Town Square block';
$string['pluginname:myaddinstance'] = 'Add the Town Square block to the dashboard';

// Error strings.
$string['invalidlettertype'] = 'Invalid function parameter, please use a valid letter type';

// Mustache templates strings.
$string['completionletterorigin'] = '{$a->modulename} should be completed until {$a->date}';
$string['basicletterorigin'] = 'A notification from {$a->modulename}:';
$string['postletternotification'] = 'New {$a->modulename} post!';
$string['postletterorigin'] = '{$a->authorname} posted in {$a->instancename} -> {$a->discussionname}:';
$string['orientationmarkercontent'] = 'Hi {$a->username}, welcome to townsquare! Today is the {$a->date}';

// Side panel filter strings.

// Filter headers.
$string['coursefilter'] = 'Course filter';
$string['timefilter'] = 'Time filter';
$string['letterfilter'] = 'Letter filter';

// Time filter options.
$string['allnotifications'] = 'All notifications';
$string['nexttwodaysnotifications'] = 'Next two days';
$string['nextfivedaysnotifications'] = 'Next five days';
$string['nextweeknotifications'] = 'Next week';
$string['nextmonthnotifications'] = 'Next month';
$string['lasttwodaysnotifications'] = 'last two days';
$string['lastfivedaysnotifications'] = 'last five days';
$string['lastweeknotifications'] = 'Last week';
$string['lastmonthnotifications'] = 'Last month';

// Letter filter options.
$string['basicletters'] = 'Basic letters';
$string['completionletters'] = 'Activity Completions';
$string['postletters'] = 'Forum Posts';

// Save button strings.
$string['savebutton'] = 'Save settings';
$string['savehelpicontext'] = 'Save Settings for the future';
$string['savemessage'] = 'Settings successfully saved!';

// Letter strings.
$string['invalidmodulename'] = 'Module name is unknown or not supported';

// Setting strings.
$string['basiclettercolor'] = 'Color for basic letters';
$string['postlettercolor'] = 'Color for post letters';
$string['completionlettercolor'] = 'Color for activity completion letters';
$string['orientationmarkercolor'] = 'Color for the orientation marker';

$string['configbasiclettercolor'] = 'Configuration for the color of the basic notification letters';
$string['configpostlettercolor'] = 'Configuration for the color of the post letters';
$string['configcompletionlettercolor'] = 'Configuration for the color of the activity completion letters';
$string['configorientationmarkercolor'] = 'Configuration for the color of the orientation marker';
