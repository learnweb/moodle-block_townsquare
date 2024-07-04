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

$string['assignduemessage'] = 'Assignment is due until {$a->time}';
$string['assigngradingduemessage'] = 'Assignment is due to be graded until {$a->time}';
$string['basicletterorigin'] = 'A notification from {$a->instancename}:';
$string['chattimemessage'] = 'The next chat time is today at {$a->time}';
$string['choiceclosemessage'] = 'Please vote until {$a->time}. Afterwards the choice is closed';
$string['choiceopenmessage'] = 'Voting is possible from {$a->time} onwards';
$string['completionletterorigin'] = '{$a->modulename} should be completed until today';
$string['dataclosemessage'] = 'Please submit your entries until {$a->time}. The database closes afterwards';
$string['dataopenmessage'] = 'The database opens today';
$string['feedbackclosemessage'] = 'Writing feedback is possible until {$a->time}';
$string['feedbackopenmessage'] = 'Writing feedback is possible from {$a->time} onwards';
$string['forumduemessage'] = 'The forum is due until {$a->time}';
$string['invalidmodulename'] = 'Module name is unknown or not supported';
$string['lessonclosemessage'] = 'The lesson ends today at {$a->time}';
$string['lessonopenmessage'] = 'The lesson opens today at {$a->time}';
$string['orientationmarkercontent'] = 'Hi {$a->username}, welcome to townsquare! Today is the {$a->date}';
$string['pluginname'] = 'Town Square';
$string['pluginname:addinstance'] = 'Add the Town Square block';
$string['pluginname:myaddinstance'] = 'Add the Town Square block to the dashboard';
$string['plugintitle'] = 'Townsquare block';

$string['postletterorigin'] = '{$a->authorname} posted in {$a->instancename} -> {$a->discussionname}:';




$string['quizclosemessage'] = 'The Quiz closes today at {$a->time}';
$string['quizopenmessage'] = 'The Quiz is open from {$a->time} onwards';
$string['workshopcloseassessment'] = 'Assessments for the workshop are due until {$a->time}';
$string['workshopclosesubmission'] = 'Please submit your work until {$a->time}. The workshop closes afterwards';
$string['workshopopenassessment'] = 'The assessment phase starts today at {$a->time}';
$string['workshopopensubmission'] = 'Submissions for the workshop are possible from {$a->time} onwards';
