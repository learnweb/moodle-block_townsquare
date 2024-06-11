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
$string['pluginname'] = 'Townsquare';
$string['pluginname:addinstance'] = 'Add the Town Square block';
$string['pluginname:myaddinstance'] = 'Add the Town Square block to the dashboard';
$string['plugintitle'] = 'Town Square Block';

// Privacy strings.
$string['privacy:metadata:block_townsquare_preferences'] = 'Town Square stores the filters that a user wants to have activated.';
$string['privacy:metadata:block_townsquare_preferences:userid'] = 'The user id';
$string['privacy:metadata:block_townsquare_preferences:timefilterpast'] = 'How far back the user wants to see notifications';
$string['privacy:metadata:block_townsquare_preferences:timefilterfuture'] = 'How far into the future the user wants to see notifications';
$string['privacy:metadata:block_townsquare_preferences:basicletter'] = 'If the user wants to see basic letters';
$string['privacy:metadata:block_townsquare_preferences:completionletter'] = 'If the user wants to see completion letters';
$string['privacy:metadata:block_townsquare_preferences:postletter'] = 'If the user wants to see post letters';

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
$string['lasttwodaysnotifications'] = 'Last two days';
$string['lastfivedaysnotifications'] = 'Last five days';
$string['lastweeknotifications'] = 'Last week';
$string['lastmonthnotifications'] = 'Last month';

// Letter filter options.
$string['basicletters'] = 'Basic letters';
$string['completionletters'] = 'Activity completions';
$string['postletters'] = 'Forum posts';
$string['showmore'] = 'Show more';

// Save button strings.
$string['savebutton'] = 'Save settings';
$string['savehelpicontext'] = 'Save Settings for the future';
$string['savemessage'] = 'Settings successfully saved!';

// End of the side panel filter strings.

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

// Event strings.
$string['assignduemessage'] = 'Assignment is due until {$a->time}';
$string['assigngradingduemessage'] = 'Assignment is due to be graded until {$a->time}';
$string['chattimemessage'] = 'The next chat time is today at {$a->time}';
$string['choiceopenmessage'] = 'Voting is possible from {$a->time} onwards';
$string['choiceclosemessage'] = 'Please vote until {$a->time}. Afterwards the choice is closed';
$string['dataopenmessage'] = 'The database opens today';
$string['dataclosemessage'] = 'Please submit your entries until {$a->time}. The database closes afterwards';
$string['feedbackopenmessage'] = 'Writing feedback is possible from {$a->time} onwards';
$string['feedbackclosemessage'] = 'Writing feedback is possible until {$a->time}';
$string['forumduemessage'] = 'The forum is due until {$a->time}';
$string['lessonopenmessage'] = 'The lesson opens today at {$a->time}';
$string['lessonclosemessage'] = 'The lesson ends today at {$a->time}';
$string['quizclosemessage'] = 'The Quiz closes today at {$a->time}';
$string['quizopenmessage'] = 'The Quiz is open from {$a->time} onwards';
$string['scormopenmessage'] = 'Scorm Activity opens today';
$string['scormclosemessage'] = 'Scorm Activity closes today';
$string['workshopopensubmission'] = 'Submissions for the workshop are possible from {$a->time} onwards';
$string['workshopclosesubmission'] = 'Please submit your work until {$a->time}. The workshop closes afterwards';
$string['workshopopenassessment'] = 'The assessment phase starts today at {$a->time}';
$string['workshopcloseassessment'] = 'Assessments for the workshop are due until {$a->time}';
