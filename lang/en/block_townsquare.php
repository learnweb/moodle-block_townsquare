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

$string['allnotifications'] = 'All notifications';
$string['assignduemessage'] = 'Assignment is due until {$a->time}';
$string['assigngradingduemessage'] = 'Assignment is due to be graded until {$a->time}';
$string['basiclettercolor'] = 'Color for basic letters';
$string['basicletterorigin'] = 'A notification from {$a->instancename}:';
$string['basicletters'] = 'Basic letters';
$string['chattimemessage'] = 'The next chat time is today at {$a->time}';
$string['choiceclosemessage'] = 'Please vote until {$a->time}. Afterwards the choice is closed';
$string['choiceopenmessage'] = 'Voting is possible from {$a->time} onwards';
$string['completionlettercolor'] = 'Color for activity completion letters';
$string['completionletterorigin'] = '{$a->modulename} should be completed until today';
$string['completionletters'] = 'Activity completions';
$string['configbasiclettercolor'] = 'Configuration for the color of the basic notification letters';
$string['configcompletionlettercolor'] = 'Configuration for the color of the activity completion letters';
$string['configorientationmarkercolor'] = 'Configuration for the color of the orientation marker';
$string['configpostlettercolor'] = 'Configuration for the color of the post letters';
$string['configtimespan'] = 'Setting, in which time span townsquare will search for notification in the past and future';
$string['coursefilter'] = 'Course filter';
$string['dataclosemessage'] = 'Please submit your entries until {$a->time}. The database closes afterwards';
$string['dataopenmessage'] = 'The database opens today';
$string['feedbackclosemessage'] = 'Writing feedback is possible until {$a->time}';
$string['feedbackopenmessage'] = 'Writing feedback is possible from {$a->time} onwards';
$string['forumduemessage'] = 'The forum is due until {$a->time}';
$string['invalidlettertype'] = 'Invalid function parameter, please use a valid letter type';
$string['invalidmodulename'] = 'Module name is unknown or not supported';
$string['lastfivedaysnotifications'] = 'Last five days';
$string['lastmonthnotifications'] = 'Last month';
$string['lasttwodaysnotifications'] = 'Last two days';
$string['lastweeknotifications'] = 'Last week';
$string['lessonclosemessage'] = 'The lesson ends today at {$a->time}';
$string['lessonopenmessage'] = 'The lesson opens today at {$a->time}';
$string['letterfilter'] = 'Letter filter';
$string['nextfivedaysnotifications'] = 'Next five days';
$string['nextmonthnotifications'] = 'Next month';
$string['nexttwodaysnotifications'] = 'Next two days';
$string['nextweeknotifications'] = 'Next week';
$string['orientationmarkercolor'] = 'Color for the orientation marker';
$string['orientationmarkercontent'] = 'Hi {$a->username}, welcome to townsquare! Today is the {$a->date}';
$string['pluginname'] = 'Town Square';
$string['pluginname:addinstance'] = 'Add the Town Square block';
$string['pluginname:myaddinstance'] = 'Add the Town Square block to the dashboard';
$string['plugintitle'] = 'Townsquare block';
$string['postlettercolor'] = 'Color for post letters';
$string['postletternotification'] = 'New {$a->modulename} post!';
$string['postletterorigin'] = '{$a->authorname} posted in {$a->instancename} -> {$a->discussionname}:';
$string['postletters'] = 'Posts';
$string['privacy:metadata:block_townsquare_preferences'] = 'Town Square stores the filters that a user wants to have activated.';
$string['privacy:metadata:block_townsquare_preferences:basicletter'] = 'If the user wants to see basic letters';
$string['privacy:metadata:block_townsquare_preferences:completionletter'] = 'If the user wants to see completion letters';
$string['privacy:metadata:block_townsquare_preferences:postletter'] = 'If the user wants to see post letters';
$string['privacy:metadata:block_townsquare_preferences:timefilterfuture'] = 'How far into the future the user wants to see notifications';
$string['privacy:metadata:block_townsquare_preferences:timefilterpast'] = 'How far back the user wants to see notifications';
$string['privacy:metadata:block_townsquare_preferences:userid'] = 'The user id';
$string['privatereplyfrom'] = 'This is a private reply from you, that only you and the post author can see.';
$string['privatereplyto'] = 'This is a private reply to your post, that only you and the reply author can see';
$string['quizclosemessage'] = 'The Quiz closes today at {$a->time}';
$string['quizopenmessage'] = 'The Quiz is open from {$a->time} onwards';
$string['resetbutton'] = 'Reset Settings';
$string['resethelpicontext'] = 'Resets all filter settings to default and deletes your settings data from the database';
$string['savebutton'] = 'Save settings';
$string['savehelpicontext'] = 'By clicking on the save button, your set preferences are saved for future uses. By changing the preferences and clicking again on "save", your preferences are updated';
$string['scormclosemessage'] = 'Scorm Activity closes today';
$string['scormopenmessage'] = 'Scorm Activity opens today';
$string['showmore'] = 'Show more';
$string['timefilter'] = 'Time filter';
$string['timesixmonths'] = 'Six months';
$string['timespan'] = 'Search time span';
$string['timethreemonths'] = 'Three months';
$string['timetwomonths'] = 'Two months';
$string['workshopcloseassessment'] = 'Assessments for the workshop are due until {$a->time}';
$string['workshopclosesubmission'] = 'Please submit your work until {$a->time}. The workshop closes afterwards';
$string['workshopopenassessment'] = 'The assessment phase starts today at {$a->time}';
$string['workshopopensubmission'] = 'Submissions for the workshop are possible from {$a->time} onwards';
