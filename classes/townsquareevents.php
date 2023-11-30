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
use mod_moodleoverflow\anonymous;

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
    public $timenow;

    /** @var int timestamp from where the events should be searched */
    public $timestart;

    /** @var int timestamp until where the events should be searched */
    public $timeend;

    /** @var array ids of the courses where the events should be searched */
    public $courses;

    /**
     * Constructor of the townsquareevents class.
     * Events will be searched in the timespan of 6 months in the past and 6 months in the future.
     */
    public function __construct() {
        $this->timenow = time();
        $this->timestart = $this->timenow - 15768000;
        $this->timeend = $this->timenow + 15768000;
        $this->courses = $this->townsquare_get_courses();
    }

    /**
     * Retrieves calendar and post events, merges and sorts them.
     * @return array
     */
    public function townsquare_get_all_events_sorted(): array {
        $calendarevents = $this->townsquare_get_calendarevents();
        $postevents = $this->townsquare_get_postevents();

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
    public function townsquare_get_calendarevents(): array {
        global $DB, $USER;

        // Get all events from the last six months and the next six months.
        $calendarevents = $this->townsquare_search_events($this->timestart, $this->timeend, $this->courses);

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
    public function townsquare_get_postevents(): array {
        global $DB;

        $forumposts = false;
        $moodleoverflowposts = false;

        // Check which modules are installed and get their data.
        if ($DB->get_record('modules', ['name' => 'forum'])) {
            $forumposts = $this->townsquare_search_posts('forum', $this->courses, $this->timestart);
        }

        if ($DB->get_record('modules', ['name' => 'moodleoverflow'])) {
            $moodleoverflowposts = $this->townsquare_search_posts('moodleoverflow', $this->courses, $this->timestart);
        }

        // If no module is installed, return an empty array..
        if (!$forumposts && !$moodleoverflowposts) {
            return [];
        }

        // Return directly the posts if no other module exists.
        if (!$moodleoverflowposts) {
            $moodleoverflowposts = $this->townsquare_add_postattributes($moodleoverflowposts);
            return $forumposts;
        }

        if (!$forumposts) {
            $forumposts = $this->townsquare_add_postattributes($forumposts);
            return $moodleoverflowposts;
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
        return $this->townsquare_add_postattributes($posts);
    }

    // Helper functions.

    /**
     * Searches for posts in the forum or moodleoverflow module.
     * This is a helper function for townsquare_get_postevents().
     * @param string $modulename  The name of the module, is 'forum' or 'moodleoverflow'.
     * @param array  $courses     The ids of the courses where the posts should be searched.
     * @param int    $timestart   The timestamp from where the posts should be searched.
     * @return array
     */
    private function townsquare_search_posts($modulename, $courses, $timestart): array {
        global $DB;
        // Prepare params for sql statement.
        list($insqlcourses, $inparamscourses) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED);
        $params = ['courses' => $courses, 'timestart' => $timestart] + $inparamscourses;
        // Set begin of sql statement.
        $begin = "SELECT (ROW_NUMBER() OVER (ORDER BY posts.id)) AS row_num, ";

        // Set the select part of the sql that is always the same.
        $middle = "module.name AS localname,
                   discuss.course AS courseid,
                   posts.id AS postid,
                   posts.discussion AS postdiscussion,
                   posts.parent AS postparent,
                   posts.userid AS postuserid,
                   posts.created AS postcreated,
                   discuss.name AS discussionsubject,
                   posts.message AS postmessage ";

        // Extend the strings for the 2 module cases.
        if ($modulename == 'forum') {
            $begin .= "'forum' AS modulename, module.id AS forumid,";
            $middle .= "FROM {forum_posts} posts
                        JOIN {forum_discussions} discuss ON discuss.id = posts.discussion
                        JOIN {forum} module ON module.id = discuss.forum ";
        } else if ($modulename == 'moodleoverflow') {
            $begin .= "'moodleoverflow' AS modulename, module.id AS moodleoverflowid, ";
            $middle .= "FROM {moodleoverflow_posts} posts
                        JOIN {moodleoverflow_discussions} discuss ON discuss.id = posts.discussion
                        JOIN {moodleoverflow} module ON module.id = discuss.moodleoverflow ";
        }

        // Set the where clause of the string.
        $end = "WHERE discuss.course $insqlcourses
                AND posts.created > :timestart
                ORDER BY posts.created DESC;";

        // Concatenate all strings.
        $sql = $begin . $middle . $end;

        // Get all posts.
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Searches for events in the events table, that are relevant to the timeline.
     * This is a helper function for townsquare_get_calendarevents().
     * @param int $timestart The time from where the events should be searched. Not equal to timestart in the database events table.
     * @param int $timeend   The time until where the events should be searched.
     * @param array $courses The ids of the courses where the events should be searched.
     * @return array
     * @throws dml_exception
     */
    private function townsquare_search_events($timestart, $timeend, $courses): array {
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
                      AND ( e.instance != 0 AND e.visible = 1)
                ORDER BY e.timestart DESC;";

        // Get all events.
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Gets the id of all courses where the current user is enrolled
     * @return array
     */
    private function townsquare_get_courses(): array {
        global $USER;

        $enrolledcourses = enrol_get_all_users_courses($USER->id);
        $courses = [];
        foreach ($enrolledcourses as $enrolledcourse) {
            $courses[] = $enrolledcourse->id;
        }

        return $courses;
    }

    /**
     * Adds the eventtype, coursemoduleid and anonymous setting (if needed) to the posts.
     * @param array $posts
     * @return void
     */
    private function townsquare_add_postattributes($posts) {
        global $DB;
        foreach ($posts as $post) {
            $post->eventtype = 'post';
            if ($post->modulename == 'moodleoverflow') {
                $moodleoverflow = $DB->get_record('moodleoverflow', ['id' => $post->moodleoverflowid]);
                $discussion = $DB->get_record('moodleoverflow_discussions', ['id' => $post->postdiscussion]);
                $post->anonymous = anonymous::is_post_anonymous($discussion, $moodleoverflow, $post->postuserid);
                $post->coursemoduleid = get_coursemodule_from_instance($post->modulename, $post->moodleoverflowid,
                    $post->courseid)->id;
            } else {
                $post->coursemoduleid = get_coursemodule_from_instance($post->modulename, $post->forumid, $post->courseid)->id;
            }
        }
        return $posts;
    }
}
