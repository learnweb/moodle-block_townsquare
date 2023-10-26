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

    /** @var int timestamp from where the events should be searched */
    public $starttime;

    /** @var int timestamp until where the events should be searched */
    public $endtime;

    /** @var array ids of the courses where the events should be searched */
    public $courses;

    /**
     * Constructor of the townsquareevents class
     */
    public function __construct() {
        $this->starttime = time() - 15768000;
        $this->endtime = time() + 15768000;
        $this->courses = $this->townsquare_get_courses();
    }

    /**
     * Retrieves calendar and post events, merges and sorts them
     * @return array
     */
    public function townsquare_get_all_events_sorted() {
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
    public function townsquare_get_calendarevents() {
        global $DB, $USER;
        // Get all events from the last six months and the next six months.
        $calendarevents = calendar_get_events($this->starttime, $this->endtime, true, true, $this->courses);

        // Add the course module id to every event.
        foreach ($calendarevents as $calendarevent) {
            $calendarevent->coursemoduleid = get_coursemodule_from_instance($calendarevent->modulename, $calendarevent->instance,
                                                                            $calendarevent->courseid)->id;
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

        return array_reverse($calendarevents, true);
    }

    /**
     * Function to get the newest posts from modules like the forum or moodleoverflow.
     *
     * The events are sorted in descending order by time created (newest event first)
     * @return array | false;
     */
    public function townsquare_get_postevents() {
        global $DB;

        $forumposts = false;
        $moodleoverflowposts = false;

        // Check which modules are installed and get their data.
        if ($DB->get_record('modules', ['name' => 'forum'])) {
            $forumposts = $this->townsquare_search_posts('forum', 'discuss.forum', 'forumid', 'forum_posts',
                'forum_discussions', $this->courses, $this->starttime);
        }

        if ($DB->get_record('modules', ['name' => 'moodleoverflow'])) {
            $moodleoverflowposts = $this->townsquare_search_posts('moodleoverflow', 'discuss.moodleoverflow',
                'moodleoverflowid', 'moodleoverflow_posts',
                'moodleoverflow_discussions', $this->courses, $this->starttime);
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
    private function townsquare_search_posts($modulename, $localid, $localidname, $posts, $discussions,
                                             $courses, $starttime) {
        global $DB;

        $sql = 'SELECT (ROW_NUMBER() OVER (ORDER BY posts.id)) AS row_num,
                       "' . $modulename . '" AS modulename,
                       discuss.course AS courseid,
                       module.id AS ' . $localidname . ',
                       module.name AS localname,
                       posts.id AS postid,
                       posts.discussion AS postdiscussion,
                       posts.parent AS postparent,
                       posts.userid AS postuserid,
                       posts.created AS postcreated,
                       discuss.name AS discussionsubject,
                       posts.message AS postmessage
                FROM {' . $posts .  '} posts
                JOIN {' . $discussions . '} discuss ON discuss.id = posts.discussion
                JOIN {' . $modulename . '} module ON module.id = discuss.' . $modulename . '
                WHERE discuss.course IN (' . implode(",", $courses) . ')
                      AND posts.created > ' . $starttime . '
                ORDER BY posts.created DESC ;';

        // Get all posts.
        return $DB->get_records_sql($sql);
    }

    /**
     * Gets the id of all courses where the current user is enrolled
     * @return array
     */
    private function townsquare_get_courses() {
        global $USER;

        $enrolledcourses = enrol_get_all_users_courses($USER->id);
        $courses = [];
        foreach ($enrolledcourses as $enrolledcourse) {
            $courses[] = $enrolledcourse->id;
        }

        return $courses;
    }
}
