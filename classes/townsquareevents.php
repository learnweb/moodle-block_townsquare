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
 * Class to get relevant events.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_townsquare;

defined('MOODLE_INTERNAL') || die();

use dml_exception;
use mod_moodleoverflow\anonymous;
global $CFG;
require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * Class to get relevant events.
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
     * Constructor of the townsquareevents class
     */
    public function __construct() {
        $this->timenow = time();
        $this->timestart = $this->timenow - 15768000;
        $this->timeend = $this->timenow + 15768000;
        $this->courses = $this->townsquare_get_courses();
    }

    /**
     * Retrieves calendar and post events, merges and sorts them
     * @return array
     */
    public function townsquare_get_all_events_sorted() : array {
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
    public function townsquare_get_calendarevents() : array {
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
                    !has_capability('mod/assign:grade', \context_module::instance($calendarevent->coursemoduleid))) {

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
     * @return array | false;
     */
    public function townsquare_get_postevents() : array {
        global $DB;

        $forumposts = false;
        $moodleoverflowposts = false;

        // Check which modules are installed and get their data.
        if ($DB->get_record('modules', ['name' => 'forum'])) {
            $forumposts = $this->townsquare_search_posts('forum', 'forumid', 'forum_posts',
                'forum_discussions', $this->courses, $this->timestart);
        }

        if ($DB->get_record('modules', ['name' => 'moodleoverflow'])) {
            $moodleoverflowposts = $this->townsquare_search_posts('moodleoverflow', 'moodleoverflowid', 'moodleoverflow_posts',
                'moodleoverflow_discussions', $this->courses, $this->timestart);
        }

        // If no module is installed, return false.
        if (!$forumposts && !$moodleoverflowposts) {
            return false;
        }

        // Return directly the posts if no other module exists.
        if (!$moodleoverflowposts) {
            return $forumposts;
        }

        if (!$forumposts) {
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

        // Add an event type to the posts and add the anonymous setting to the moodleoverflow posts.
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

        // Return the posts.
        return $posts;
    }

    // Helper functions.

    /**
     * Searches for posts in the forum or moodleoverflow module.
     * This is a helper function for townsquare_get_postevents().
     * @param string $modulename  The name of the module, is 'forum' or 'moodleoverflow'.
     * @param string $localid     The internal id of the modules instance.
     * @param string $localidname The name of the module instances id, i 'forumid' or 'moodleoverflowid'.
     * @param string $posts The name of the posts table, is 'forum_posts' or 'moodleoverflow_posts'.
     * @param string $discussions The name of the discussions table, is 'forum_discussions' or 'moodleoverflow_discussions'.
     * @param array  $courses The ids of the courses where the posts should be searched.
     * @param int    $starttime   The timestamp from where the posts should be searched.
     * @return array
     */
    private function townsquare_search_posts($modulename, $localidname, $posts, $discussions, $courses, $starttime) : array {
        global $DB;
        $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY posts.id)) AS row_num,
                       '" . $modulename . "' AS modulename,
                       discuss.course AS courseid,
                       module.id AS " . $localidname . ",
                       module.name AS localname,
                       posts.id AS postid,
                       posts.discussion AS postdiscussion,
                       posts.parent AS postparent,
                       posts.userid AS postuserid,
                       posts.created AS postcreated,
                       discuss.name AS discussionsubject,
                       posts.message AS postmessage
                FROM {" . $posts .  "} posts
                JOIN {" . $discussions . "} discuss ON discuss.id = posts.discussion
                JOIN {" . $modulename . "} module ON module.id = discuss." . $modulename . "
                WHERE discuss.course IN (" . implode(",", $courses) . ")
                      AND posts.created > " . $starttime . "
                ORDER BY posts.created DESC ;";

        // Get all posts.
        return $DB->get_records_sql($sql);
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
    private function townsquare_search_events($timestart, $timeend, $courses) : array {
        global $DB;

        $sql = "SELECT e.id, e.name, e.courseid, e.groupid, e.userid, e.modulename, e.instance, e.eventtype, e.timestart, e.visible
                FROM {event} e JOIN {modules} m ON e.modulename = m.name
                WHERE (e.timestart >= " . $timestart . " OR e.timestart+e.timeduration > " . $timestart . " )
                      AND e.timestart <= " . $timeend . "
                      AND e.courseid IN (" . implode(',', $courses) . " )
                      AND (e.name NOT LIKE '" .'0'. "' AND e.eventtype NOT LIKE '" .'0'. "' )
                      AND ( e.instance != 0 AND e.userid != 0 AND e.visible = 1)
                ORDER BY e.timestart DESC;";

        // Get all events.
        return $DB->get_records_sql($sql);
    }

    /**
     * Gets the id of all courses where the current user is enrolled
     * @return array
     */
    private function townsquare_get_courses() : array {
        global $USER;

        $enrolledcourses = enrol_get_all_users_courses($USER->id);
        $courses = [];
        foreach ($enrolledcourses as $enrolledcourse) {
            $courses[] = $enrolledcourse->id;
        }

        return $courses;
    }
}
