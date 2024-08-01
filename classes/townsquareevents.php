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
 * Class to get relevant events from courses the user is enrolled to..
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_townsquare;

defined('MOODLE_INTERNAL') || die();

use context_module;
use core_component;
use dml_exception;
use moodle_url;
use function local_townsquaresupport\townsquaresupport_get_subplugin_events;

global $CFG;
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/blocks/townsquare/lib.php');
require_once($CFG->dirroot . '/blocks/townsquare/locallib.php');

/**
 * Class to get events and posts that will be shown in the townsquare block..
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class townsquareevents {

    /** @var int timestamp of the current time */
    public int $timenow;

    /** @var int timestamp from where the events should be searched */
    public int $timestart;

    /** @var int timestamp until where the events should be searched */
    public int $timeend;

    /** @var array ids of the courses where the events should be searched */
    public array $courses;

    /**
     * Constructor of the townsquareevents class.
     * Events will be searched in the timespan of 6 months in the past and 6 months in the future.
     */
    public function __construct() {
        $this->timenow = time();
        $this->timestart = townsquare_get_timestart();
        $this->timeend = townsquare_get_timeend();
        $this->courses = townsquare_get_courses();
    }

    /**
     * Retrieves calendar, post events, merges and sorts them.
     * @return array
     */
    public function get_all_events_sorted(): array {
        global $CFG;
        $coreevents = $this->get_coreevents();
        $postevents = $this->get_postevents();

        // Check if the townsquaresupport plugin is installed.
        $localplugins = \core_plugin_manager::instance()->get_plugins_of_type('local');
        $subpluginevents = [];
        if (array_key_exists('townsquaresupport', $localplugins)) {
            require_once($CFG->dirroot . '/local/townsquaresupport/lib.php');
            $subpluginevents = townsquaresupport_get_subplugin_events();
        }

        // Return the events in a sorted order.
        $events = array_merge($coreevents, $postevents, $subpluginevents);
        return townsquare_mergesort($events);
    }

    /**
     * Function to get events/notifications from core plugins for the current user.
     *
     * The events are sorted in descending order by time created (newest event first)
     * @return array
     */
    public function get_coreevents(): array {
        global $DB;

        // Get all events from the last six months and the next six months.
        $coreevents = $this->get_events_from_db($this->timestart, $this->timeend, $this->courses);

        // Filter the events and add the instancename.
        foreach ($coreevents as $coreevent) {
            // Filter out events that are not relevant for the user.
            if (townsquare_filter_availability($coreevent) ||
                ($coreevent->modulename == "assign" && $this->filter_assignment($coreevent)) ||
                ($coreevent->eventtype == "expectcompletionon" && townsquare_filter_activitycompletions($coreevent))) {
                unset($coreevents[$coreevent->id]);
                continue;
            }

            // Add the name of the instance to the event.
            $coreevent->instancename = $DB->get_field($coreevent->modulename, 'name', ['id' => $coreevent->instance]);

            // Modify the content of the event if needed.
            townsquare_check_coreevent($coreevent);
        }

        return $coreevents;
    }

    /**
     * Function to get the newest posts from modules like the forum or moodleoverflow.
     *
     * The events are sorted in descending order by time created (newest event first)
     * @return array;
     */
    public function get_postevents(): array {
        global $DB;

        // If forum is not installed or not activated, return empty array.
        if (!$DB->get_record('modules', ['name' => 'forum', 'visible' => 1])) {
            return [];
        }

        $forumposts = $this->get_forumposts_from_db($this->courses, $this->timestart);

        foreach ($forumposts as $post) {
            if (townsquare_filter_availability($post) || $this->filter_forum_privatepost($post)) {
                unset($forumposts[$post->row_num]);
            }

            // Add a links and the authors picture.
            $post->linktopost = new moodle_url('/mod/forum/discuss.php',
                                ['d' => $post->postdiscussion], 'p' . $post->postid);
            $post->linktoauthor = new moodle_url('/user/view.php', ['id' => $post->postuserid]);
        }

        return $forumposts;
    }

    // Helper functions.

    /**
     * Searches for posts in the forum or moodleoverflow module.
     * The sql query makes sure that the modules are installed and available..
     * This is a helper function for get_postevents().
     * @param string $modulename  The name of the module, is 'forum' or 'moodleoverflow'.
     * @param array  $courses     The ids of the courses where the posts should be searched.
     * @param int    $timestart   The timestamp from where the posts should be searched.
     * @return array
     */
    private function get_forumposts_from_db($courses, $timestart): array {
        global $DB;
        // Prepare params for sql statement.
        if ($courses == []) {
            return [];
        }
        list($insqlcourses, $inparamscourses) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED);
        $params = ['courses' => $courses, 'timestart' => $timestart] + $inparamscourses;

        $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY posts.id)) AS row_num,
                    'forum' AS modulename,
                    module.id AS instanceid,
                    'post' AS eventtype,
                    cm.id AS coursemoduleid,
                    cm.availability AS availability,
                    module.name AS instancename,
                    discuss.course AS courseid,
                    discuss.userid AS discussionuserid,
                    discuss.name AS discussionsubject,
                    u.firstname AS postuserfirstname,
                    u.lastname AS postuserlastname,
                    posts.id AS postid,
                    posts.discussion AS postdiscussion,
                    posts.parent AS postparentid,
                    posts.userid AS postuserid,
                    posts.created AS timestart,
                    posts.message AS postmessage,
                    posts.messageformat AS postmessageformat,
                    posts.privatereplyto AS postprivatereplyto
                FROM {forum_posts} posts
                JOIN {forum_discussions} discuss ON discuss.id = posts.discussion
                JOIN {forum} module ON module.id = discuss.forum
                JOIN {modules} modules ON modules.name = 'forum'
                JOIN {user} u ON u.id = posts.userid
                JOIN {course_modules} cm ON (cm.course = module.course AND cm.module = modules.id
                                                                       AND cm.instance = module.id)
                WHERE discuss.course $insqlcourses
                    AND posts.created > :timestart
                    AND cm.visible = 1
                    AND modules.visible = 1
                ORDER BY posts.created DESC;";

        // Get all posts.
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Searches for events in the events table, that are relevant to the timeline.
     * This is a helper function for get_coreevents().
     * @param int $timestart The time from where the events should be searched. Not equal to timestart in the database events table.
     * @param int $timeend   The time until where the events should be searched.
     * @param array $courses The ids of the courses where the events should be searched.
     * @return array
     * @throws dml_exception
     */
    private function get_events_from_db($timestart, $timeend, $courses): array {
        global $DB;

        // As there are no events without courses, return an empty array.
        if ($courses == []) {
            return [];
        }

        // Due to compatability reasons, only events from core modules are shown.
        $modules = ['assign', 'book', 'chat', 'choice', 'data', 'feedback', 'file', 'folder', 'forum', 'glossary',
                    'h5pactivity', 'imscp', 'label', 'lesson', 'lti', 'page', 'quiz', 'resource', 'scorm', 'survey', 'url',
                    'wiki', 'workshop', ];

        // Prepare params for sql statement.
        list($insqlcourses, $inparamscourses) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED);
        list($insqlmodules, $inparamsmodules) = $DB->get_in_or_equal($modules, SQL_PARAMS_NAMED);
        $params = ['timestart' => $timestart, 'timeduration' => $timestart,
                   'timeend' => $timeend, 'courses' => $courses, ] + $inparamscourses + $inparamsmodules;

        // Set the sql statement.
        $sql = "SELECT e.id, e.name, e.courseid, cm.id AS coursemoduleid, cm.availability AS availability, e.groupid, e.userid,
                       e.modulename, e.instance, e.eventtype, e.timestart, e.timemodified, e.visible
                FROM {event} e
                JOIN {modules} m ON e.modulename = m.name
                JOIN {course_modules} cm ON (cm.course = e.courseid AND cm.module = m.id AND cm.instance = e.instance)
                WHERE (e.timestart >= :timestart OR e.timestart+e.timeduration > :timeduration)
                      AND e.timestart <= :timeend
                      AND e.courseid $insqlcourses
                      AND e.modulename $insqlmodules
                      AND m.visible = 1
                      AND (e.name NOT LIKE '" .'0'. "' AND e.eventtype NOT LIKE '" .'0'. "' )
                      AND (e.instance <> 0 AND e.visible = 1)
                ORDER BY e.timestart DESC";

        // Get all events.
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Filter that checks if the event needs to be filtered out for the current user.
     * Applies to assignment events.
     * @param object $coreevent coreevent that is checked
     * @return bool true if the event needs to filtered out, false if not.
     */
    private function filter_assignment($coreevent): bool {
        global $DB;
        $assignment = $DB->get_record('assign', ['id' => $coreevent->instance]);
        $type = $coreevent->eventtype;
        // Check if the assign is longer than a week closed.
        $overduecheck = ($type == "due" || $type == "gradingdue") && ($this->timenow >= ($coreevent->timestart + 604800));

        // Check if the user is someone without grading capability.
        $cannotgradecheck = $coreevent->eventtype == "gradingdue" && !has_capability('mod/assign:grade',
                                                                        context_module::instance($coreevent->coursemoduleid));
        // Check if the assignment is not open yet.
        $stillclosedcheck = $assignment->allowsubmissionsfromdate >= $this->timenow;

        if ($overduecheck || $cannotgradecheck || $stillclosedcheck) {
            return true;
        }
        return false;
    }

    /**
     * Filter that checks if a forum posts is a private reply that only the author and the receiver can see.
     * If the post is a private reply but is not filtered out, the functions adds to attributes to the post object.
     * Applies to forum posts.
     * @param object $forumpost The post that is checked.
     * @return bool true if the posts needs to be filtered out, false if not.
     */
    private function filter_forum_privatepost(&$forumpost): bool {
        global $USER;
        // Check if the postuserid or the userid from the private attribute is the current user.
        $isprivatemessage = $forumpost->postprivatereplyto != 0;
        $isauthor = $forumpost->postuserid == $USER->id;
        $isreceiver = $forumpost->postprivatereplyto == $USER->id;

        // Filter out the post if the current user is not the author or the receiver.
        if ($isprivatemessage && !$isauthor && !$isreceiver) {
            return true;
        }

        // Add attributes to know if the private reply is from or to the current user.
        $forumpost->privatereplyfrom = $isprivatemessage && $isauthor;
        $forumpost->privatereplyto = $isprivatemessage && $isreceiver;

        // Add attributes as the posts is further processed.

        return false;
    }

}
