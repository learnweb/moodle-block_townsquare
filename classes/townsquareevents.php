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
use dml_exception;

global $CFG;
require_once($CFG->dirroot . '/calendar/lib.php');
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
        $this->timestart = $this->timenow - 15768000;
        $this->timeend = $this->timenow + 15768000;
        $this->courses = townsquare_get_courses();
    }

    /**
     * Retrieves calendar and post events, merges and sorts them.
     * @return array
     */
    public function get_all_events_sorted(): array {
        $coreevents = $this->get_coreevents();
        $postevents = $this->get_postevents();
        $subpluginevents = $this->get_subpluginevents();

        // Merge the events in a sorted order.
        $events = $coreevents + $postevents + $subpluginevents;
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
                ($coreevent->eventtype == "expectcompletionon" && $this->filter_activitycompletions($coreevent))) {
                unset($coreevents[$coreevent->id]);
                continue;
            }

            // Add the name of the instance to the event.
            $coreevent->instancename = $DB->get_field($coreevent->modulename, 'name', ['id' => $coreevent->instance]);
        }

        return $coreevents;
    }

    /**
     * Function to get events/notifications from other plugins than core plugins for the current user.
     * This function checks for subplugins and calls their get_events() function.
     *
     * The events are sorted in descending order by time created (newest event first)
     * @return array
     */
    public function get_subpluginevents(): array {
        // Get all available subplugins.
        $events = [];
        $subplugins = \core_plugin_manager::instance()->get_plugins_of_type('supportedmodules');
        var_dump($subplugins);
        foreach ($subplugins as $subplugin) {
            $events += $subplugin->get_events();
        }

        // Sort the events and return them.
        return townsquare_mergesort($events);
    }

    /**
     * Function to get the newest posts from modules like the forum or moodleoverflow.
     *
     * The events are sorted in descending order by time created (newest event first)
     * @return array;
     */
    public function get_postevents(): array {
        global $DB;

        $forumposts = [];
        $moodleoverflowposts = [];

        // Check which modules are installed and activated and get their data.
        if ($DB->get_record('modules', ['name' => 'forum', 'visible' => 1])) {
            $forumposts = $this->get_posts_from_db('forum', $this->courses, $this->timestart);
        }
        // TODO: implement support for moodleoverflow in a subplugin.
        if ($DB->get_record('modules', ['name' => 'moodleoverflow', 'visible' => 1])) {
            //$moodleoverflowposts = $this->get_posts_from_db('moodleoverflow', $this->courses, $this->timestart);
        }

        if (empty($forumposts) && empty($moodleoverflowposts)) {
            return [];
        }

        // Merge the posts in a sorted order. While merging, filter out irrelevant posts and add relevant attributes if necessary.
        $posts = [];
        $numberofposts = count($forumposts) + count($moodleoverflowposts);
        reset($forumposts);
        reset($moodleoverflowposts);
        for ($i = 0; $i < $numberofposts; $i++) {
            // Filter unavailable posts.
            // Iterate until the first post that is available. Decrement the number of posts each time a post is filtered.
            while (current($forumposts) && townsquare_filter_availability(current($forumposts))) {
                next($forumposts);
                $numberofposts--;
            }
            while (current($moodleoverflowposts) && townsquare_filter_availability(current($moodleoverflowposts))) {
                next($moodleoverflowposts);
                $numberofposts--;
            }
            // If there no posts left after filtering, break.
            if ($i >= $numberofposts) {
                break;
            }

            // Merge.
            if (current($forumposts) && current($moodleoverflowposts)) {
                if (current($forumposts)->timestart > current($moodleoverflowposts)->timestart) {
                    $posts[$i] = current($forumposts);
                    next($forumposts);
                } else {
                    $posts[$i] = current($moodleoverflowposts);
                    next($moodleoverflowposts);
                }
            } else if (current($forumposts)) {
                $posts[$i] = current($forumposts);
                next($forumposts);
            } else {
                $posts[$i] = current($moodleoverflowposts);
                next($moodleoverflowposts);
            }
        }

        // Add an event type to the posts and add the anonymous setting to the moodleoverflow posts. Then return it.
        return $posts;
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
    private function get_posts_from_db($modulename, $courses, $timestart): array {
        global $DB;
        // Prepare params for sql statement.
        list($insqlcourses, $inparamscourses) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED);
        $params = ['courses' => $courses, 'timestart' => $timestart] + $inparamscourses;
        // Set begin of sql statement.
        $begin = "SELECT (ROW_NUMBER() OVER (ORDER BY posts.id)) AS row_num, ";

        // Set the select part of the sql that is always the same.
        $middle = "'post' AS eventtype,
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
                   posts.message AS postmessage ";

        // Extend the strings for the 2 module cases.
        if ($modulename == 'forum') {
            $begin .= "'forum' AS modulename, module.id AS instanceid,";
            $middle .= "FROM {forum_posts} posts
                        JOIN {forum_discussions} discuss ON discuss.id = posts.discussion
                        JOIN {forum} module ON module.id = discuss.forum
                        JOIN {modules} modules ON modules.name = 'forum' ";

        } else if ($modulename == 'moodleoverflow') {
            $begin .= "'moodleoverflow' AS modulename, module.id AS instanceid, module.anonymous AS anonymoussetting, ";
            $middle .= "FROM {moodleoverflow_posts} posts
                        JOIN {moodleoverflow_discussions} discuss ON discuss.id = posts.discussion
                        JOIN {moodleoverflow} module ON module.id = discuss.moodleoverflow
                        JOIN {modules} modules ON modules.name = 'moodleoverflow' ";
        }

        // Extension of the middle string.
        $middle .= "JOIN {user} u ON u.id = posts.userid
                    JOIN {course_modules} cm ON (cm.course = module.course AND cm.module = modules.id
                                                                           AND cm.instance = module.id) ";

        // Set the where clause of the string.
        $end = "WHERE discuss.course $insqlcourses
                AND posts.created > :timestart
                AND cm.visible = 1
                AND modules.visible = 1
                ORDER BY posts.created DESC;";

        // Concatenate all strings.
        $sql = $begin . $middle . $end;

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

        // Due to compatability reasons, only events from supported modules are shown.
        // Supported modules are: core modules and custom additional modules.
        $coremodules = ['assign', 'book', 'chat', 'choice', 'data', 'feedback', 'folder', 'forum', 'glossary', 'h5pactivity',
                     'imscp', 'label', 'lesson', 'lti', 'page', 'quiz', 'resource', 'scorm', 'survey', 'url', 'wiki', 'workshop', ];
        $additionalmodules = ['moodleoverflow', 'ratingallocate'];
        $modules = $coremodules + $additionalmodules;

        // Prepare params for sql statement.
        list($insqlcourses, $inparamscourses) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED);
        list($insqlmodules, $inparamsmodules) = $DB->get_in_or_equal($modules, SQL_PARAMS_NAMED);
        $params = ['timestart' => $timestart, 'timeduration' => $timestart,
                   'timeend' => $timeend, 'courses' => $courses, ] + $inparamscourses + $inparamsmodules;

        // Set the sql statement.
        $sql = "SELECT e.id, e.name, e.courseid, cm.id AS coursemoduleid, cm.availability AS availability, e.groupid, e.userid,
                       e.modulename, e.instance, e.eventtype, e.timestart, e.visible
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
     * help function to sort and merge 2 array of events.
     * Note that each of the arrays are already sorted in descending order by time created (newest event first).
     * @param array $leftarray  The first array of events.
     * @param array $rightarray The second array of events.
     *
     * @return array
     */
    private function merge_events($leftarray, $rightarray, ): array {
        // TODO: merge sort events.
        return [];
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
        $nogradecapabilitycheck = $coreevent->eventtype == "gradingdue" && !has_capability('mod/assign:grade',
                                                                        context_module::instance($coreevent->coursemoduleid));
        // Check if the assignment is not open yet.
        $stillclosedcheck = $assignment->allowsubmissionsfromdate >= $this->timenow;

        if ($overduecheck || $nogradecapabilitycheck || $stillclosedcheck) {
            return true;
        }
        return false;
    }

    /**
     * Filter that checks if the event needs to be filtered out for the current user because it is already completed..
     * Applies to activity completion events.
     * @param object $coreevent coreevent that is checked
     * @return bool true if the event needs to filtered out, false if not.
     */
    private function filter_activitycompletions($coreevent): bool {
        global $DB, $USER;
        if ($completionstatus = $DB->get_record('course_modules_completion',
                                                ['coursemoduleid' => $coreevent->coursemoduleid, 'userid' => $USER->id])) {
            if ($completionstatus->completionstate != 0) {
                return true;
            }
        }
        return false;
    }
}
