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

require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * Class to get relevant events.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class townsquareevents {

    /** @var array events from the moodle database */
    public $events;

    /** @var int timestamp from where the events should be searched */
    public $starttime;

    /** @var int timestamp until where the events should be searched */
    public $endtime;

    /** @var array ids of the courses where the events should be searched */
    public $courses;

    public function __construct() {
        $this->events = [];
        $this->starttime = time() - 15768000;
        $this->starttime = 1690000000;
        $this->endtime = time() + 15768000;
        $this->courses = $this->townsquare_get_courses();
    }

    /**
     * Function to get events from that are in the calendar for the current user.
     * @return array $events
     */
    public function townsquare_get_calendarevents() {
        global $USER;

        // Get all events from the last six months and the next six months.
        return calendar_get_events($this->starttime, $this->endtime, true, true, $courses);
    }

    /**
     * Function to get the newest posts from modules like the forum or moodleoverflow.
     *
     * @return array | false;
     */
    public function townsquare_get_postevents() {
        global $DB;

        $forumposts = false;
        $moodleoverflowposts = false;

        // Check first which modules are installed.
        if ($DB->get_record('modules', ['name' => 'forum'])) {
            $forumposts = true;
        }

        if ($DB->get_record('modules', ['name' => 'moodleoverflow'])) {
            $moodleoverflowposts = true;
        }

        // If no module is installed, return false.
        if (!$forumposts && !$moodleoverflowposts) {
            return false;
        }

        // Get the posts from the modules and return it directly if no other module exists.
        if ($forumposts) {
            $forumposts = $this->townsquare_search_posts('forum', 'discuss.forum', 'forumid', 'forum_posts',
                                                        'forum_discussions', $this->courses, $this->starttime);
            if (!$moodleoverflowposts) {
                return $forumposts;
            }
        }

        if ($moodleoverflowposts) {
            $moodleoverflowposts = $this->townsquare_search_posts('moodleoverflow', 'discuss.moodleoverflow',
                                                                  'moodleoverflowid', 'moodleoverflow_posts',
                                                                  'moodleoverflow_discussions', $this->courses, $this->starttime);
            if (!$forumposts) {
                return $moodleoverflowposts;
            }
        }

        // Merge the posts in a sorted order.
        $posts = [];
        reset($forumposts);
        reset($moodleoverflowposts);
        for ($i = 0; $i < (count($forumposts) + count($moodleoverflowposts)); $i++) {
            if (current($forumposts) && current($moodleoverflowposts)) {
                if (current($forumposts)->postcreated > current($moodleoverflowposts)->postcreated) {
                    $posts[$i] = current($forumposts);
                    next($forumposts);
                } else {
                    $posts[$i] = current($moodleoverflowposts);
                    next($moodleoverflowposts);
                }
            } else if (!current($forumposts)) {
                $posts[$i] = current($moodleoverflowposts);
                next($moodleoverflowposts);
            } else {
                $posts[$i] = current($forumposts);
                next($forumposts);
            }
        }

        // Add an event type to the posts.
        foreach ($posts as $post) {
            $post->eventtype = 'post';
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
     * @param string $localidname The name of the module instances id, if 'forumid' or 'moodleoverflowid'.
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
                       ' . $localid . ' AS ' . $localidname . ',
                       posts.id AS postid,
                       posts.discussion AS postdiscussion,
                       posts.parent AS postparent,
                       posts.userid AS postuserid,
                       posts.created AS postcreated,
                       discuss.name AS discussionsubject,
                       posts.message AS postmessage
                FROM {' . $posts .  '} posts
                JOIN {' . $discussions . '} discuss ON discuss.id = posts.discussion
                WHERE discuss.course IN (' . implode(',', $courses) . ')
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
