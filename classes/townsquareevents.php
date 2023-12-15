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
        $this->courses = $this->get_courses();
    }

    /**
     * Retrieves calendar and post events, merges and sorts them.
     * @return array
     */
    public function get_all_events_sorted(): array {
        $calendarevents = $this->get_calendarevents();
        $postevents = $this->get_postevents();

        // Merge the events in a sorted order.
        $events = [];
        $numberofevents = count($calendarevents) + count($postevents);
        for ($i = 0; $i < $numberofevents; $i++) {
            if (current($calendarevents) && current($postevents)) {
                if (current($calendarevents)->timestart > current($postevents)->postcreated) {
                    $events[$i] = current($calendarevents);
                    next($calendarevents);
                } else {
                    $events[$i] = current($postevents);
                    next($postevents);
                }
            } else if (current($calendarevents)) {
                $events[$i] = current($calendarevents);
                next($calendarevents);
            } else {
                $events[$i] = current($postevents);
                next($postevents);
            }
        }

        return $events;
    }

    /**
     * Function to get events from that are in the calendar for the current user.
     *
     * The events are sorted in descending order by time created (newest event first)
     * @return array
     */
    public function get_calendarevents(): array {
        global $DB, $USER;

        // Get all events from the last six months and the next six months.
        $calendarevents = $this->get_events_from_db($this->timestart, $this->timeend, $this->courses);

        // Filter the events and add the coursemoduleid.
        foreach ($calendarevents as $calendarevent) {
            $calendarevent->coursemoduleid = get_coursemodule_from_instance($calendarevent->modulename, $calendarevent->instance,
                                                                            $calendarevent->courseid)->id;

            // Delete assign events that the user should not see.
            if ($calendarevent->modulename == "assign") {

                // If the assignment due date is over than a week, it disappears.
                if ($calendarevent->eventtype == "due" && $this->timenow >= ($calendarevent->timestart + 604800)) {
                    unset($calendarevents[$calendarevent->id]);
                    continue;
                }

                // Only people that can rate should see a gradingdue event.
                if ($calendarevent->eventtype == "gradingdue" &&
                    !has_capability('mod/assign:grade', context_module::instance($calendarevent->coursemoduleid))) {

                    unset($calendarevents[$calendarevent->id]);
                    continue;
                }

                // If the assignment is not open to submit, the user should not see the event.
                $assignment = $DB->get_record('assign', ['id' => $calendarevent->instance]);
                if ($assignment->allowsubmissionsfromdate >= $this->timenow) {
                    unset($calendarevents[$calendarevent->id]);
                    continue;
                }
            }

            // Delete activity completions that are completed by the current user.
            if ($calendarevent->eventtype == "expectcompletionon") {
                if ($completionstatus = $DB->get_record('course_modules_completion',
                        ['coursemoduleid' => $calendarevent->coursemoduleid, 'userid' => $USER->id])) {
                    if ($completionstatus->completionstate != 0) {
                        unset($calendarevents[$calendarevent->id]);
                    }
                }
            }
        }

        return $calendarevents;
    }

    /**
     * Function to get the newest posts from modules like the forum or moodleoverflow.
     *
     * The events are sorted in descending order by time created (newest event first)
     * @return array;
     */
    public function get_postevents(): array {
        global $DB;

        $forumposts = false;
        $moodleoverflowposts = false;

        // Check which modules are installed and activated and get their data.
        if ($DB->get_record('modules', ['name' => 'forum', 'visible' => 1])) {
            $forumposts = $this->get_posts_from_db('forum', $this->courses, $this->timestart);
        }

        if ($DB->get_record('modules', ['name' => 'moodleoverflow', 'visible' => 1])) {
            $moodleoverflowposts = $this->get_posts_from_db('moodleoverflow', $this->courses, $this->timestart);
        }

        // If no module is available, return an empty array.
        if (!$forumposts && !$moodleoverflowposts) {
            return [];
        }

        // Return directly the posts if no other module exists.
        if (!$moodleoverflowposts) {
            return $forumposts;
        }

        if (!$forumposts) {
            return $this->add_anonymousattribute($moodleoverflowposts);
        }

        // Merge the posts in a sorted order.
        $posts = [];
        $numberofposts = count($forumposts) + count($moodleoverflowposts);
        reset($forumposts);
        reset($moodleoverflowposts);
        for ($i = 0; $i < $numberofposts; $i++) {
            if (current($forumposts) && current($moodleoverflowposts)) {
                if (current($forumposts)->postcreated > current($moodleoverflowposts)->postcreated) {
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
        return $this->add_anonymousattribute($posts);
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
                   module.name AS instancename,
                   discuss.course AS courseid,
                   discuss.userid AS discussionuserid,
                   posts.id AS postid,
                   posts.discussion AS postdiscussion,
                   posts.parent AS postparentid,
                   posts.userid AS postuserid,
                   posts.created AS postcreated,
                   discuss.name AS discussionsubject,
                   posts.message AS postmessage ";

        // Extend the strings for the 2 module cases.
        if ($modulename == 'forum') {
            $begin .= "'forum' AS modulename, module.id AS forumid,";
            $middle .= "FROM {forum_posts} posts
                        JOIN {forum_discussions} discuss ON discuss.id = posts.discussion
                        JOIN {forum} module ON module.id = discuss.forum
                        JOIN {modules} modules ON modules.name = 'forum' ";
        } else if ($modulename == 'moodleoverflow') {
            $begin .= "'moodleoverflow' AS modulename, module.id AS moodleoverflowid, module.anonymous AS anonymoussetting";
            $middle .= "FROM {moodleoverflow_posts} posts
                        JOIN {moodleoverflow_discussions} discuss ON discuss.id = posts.discussion
                        JOIN {moodleoverflow} module ON module.id = discuss.moodleoverflow
                        JOIN {modules} modules ON modules.name = 'moodleoverflow' ";
        }

        // Extension of the middle string.
        $middle .= "JOIN {course_modules} cm ON (cm.course = module.course AND cm.module = modules.id
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
     * This is a helper function for get_calendarevents().
     * @param int $timestart The time from where the events should be searched. Not equal to timestart in the database events table.
     * @param int $timeend   The time until where the events should be searched.
     * @param array $courses The ids of the courses where the events should be searched.
     * @return array
     * @throws dml_exception
     */
    private function get_events_from_db($timestart, $timeend, $courses): array {
        global $DB;

        // Prepare params for sql statement.
        list($insqlcourses, $inparamscourses) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED);
        $params = ['timestart' => $timestart, 'timeduration' => $timestart,
                   'timeend' => $timeend, 'courses' => $courses, ] + $inparamscourses;

        // Set the sql statement.
        $sql = "SELECT e.id, e.name, e.courseid, e.groupid, e.userid, e.modulename, e.instance, e.eventtype, e.timestart, e.visible
                FROM {event} e JOIN {modules} m ON e.modulename = m.name
                WHERE (e.timestart >= :timestart OR e.timestart+e.timeduration > :timeduration)
                      AND e.timestart <= :timeend
                      AND e.courseid $insqlcourses
                      AND (e.name NOT LIKE '" .'0'. "' AND e.eventtype NOT LIKE '" .'0'. "' )
                      AND ( e.instance != 0 AND e.visible = 1)";

        // TODO: Whitelist events that townsquare can handle. Other event types must be added manually.
        /*
        // List core modules that have additional calendar events.
        $coremodules = ['assign', 'chat', 'choice', 'data', 'feedback', 'forum', 'lesson', 'quiz', 'scorm', 'workshop'];

        // Check which of these modules are active.
        foreach($coremodules as $coremodule) {
            if (!$DB->get_record('modules', ['name' => $coremodule, 'visible' => 1])) {
                unset($coremodules[array_search($coremodule, $coremodules)]);
            }
        }

        // Activity completions are always shown.
        $completionsql = " (e.eventtype = 'expectcompletionon')";

        // Event types 'open' and 'close' are shown, if the module is an active coremodule.
        if (!empty($coremodules)) {
            list($insqlmodules, $inparamsmodules) = $DB->get_in_or_equal($coremodules, SQL_PARAMS_NAMED);
            $params += $inparamsmodules;
            $openeventtypesql = "((e.eventtype = 'open' OR e.eventtype = 'close') AND (e.modulename $insqlmodules))";
        }

        // Assignment event type handling.
        $params += ['timeweek' => $this->timenow - 604800];
        // TODO: Check if the assignment is aklready submitted by the user.
        $assigndue = "(e.modulename = 'assign' AND e.eventtype = 'due' AND e.timestart >= :timeweek)";
        $assigngradingdue = "e.modulename = 'assign' AND e.eventtype = 'gradingdue";

        // Chat event type handling.
        $chatevent = " (e.modulename = 'chat' AND e.eventtype = 'chattime')";

        // Workshop event type handling.
        $workshopevent = " (e.modulename = 'workshop')";

        // Event type handling of non-core modules.
        // Ratingallocate event type handling.

        // Put all different module sql handling together.
        //$sql .= " AND (". $completionsql ." OR ". $openeventtypesql ." OR ". $assigndue ." OR ".
        //                 $assigngradingdue ." OR ". $chatevent ." OR ". $workshopevent .")";
        */

        // Add the order of the sql query.
        $sql .= " ORDER BY e.timestart DESC;";

        // Get all events.
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Gets the id of all courses where the current user is enrolled
     * @return array
     */
    private function get_courses(): array {
        global $USER;

        $enrolledcourses = enrol_get_all_users_courses($USER->id, true);
        $courses = [];
        foreach ($enrolledcourses as $enrolledcourse) {
            $courses[] = $enrolledcourse->id;
        }

        return $courses;
    }

    /**
     * Adds a boolean for the anonymous status of moodleoverflow posts.
     *
     * @param array $posts
     * @return array
     */
    private function add_anonymousattribute($posts): array {
        // Save the anonymous setting of the moodleoverflows.
        $totalanonymous = [];
        $partialanonymous = [];
        $notanonymous = [];
        foreach ($posts as $post) {
            if ($post->modulename == 'moodleoverflow') {
                // Fill the corresponding array if the moodleoverflow is not known yet..
                if (!array_key_exists($post->moodleoverflowid, $totalanonymous) &&
                    !array_key_exists($post->moodleoverflowid, $partialanonymous) &&
                    !array_key_exists($post->moodleoverflowid, $notanonymous)) {
                    if ($post->anonymoussetting == \mod_moodleoverflow\anonymous::EVERYTHING_ANONYMOUS) {
                        $totalanonymous[$post->moodleoverflowid] = true;
                        $post->anonymous = true;
                    } else if ($post->anonymoussetting == \mod_moodleoverflow\anonymous::QUESTION_ANONYMOUS) {
                        $partialanonymous[$post->moodleoverflowid] = true;
                        $post->anonymous = $post->postuserid == $post->discussionuserid;
                    } else {
                        $notanonymous[$post->moodleoverflowid] = true;
                        $post->anonymous = false;
                    }
                } else {
                    if (array_key_exists($post->moodleoverflowid, $totalanonymous)) {
                        $post->anonymous = true;
                    } else if (array_key_exists($post->moodleoverflowid, $partialanonymous)) {
                        $post->anonymous = $post->postuserid == $post->discussionuserid;
                    } else {
                        $post->anonymous = false;
                    }
                }
                unset($post->anonymoussetting);
                unset($post->discussionuserid);
            }
        }
        return $posts;
    }
}
