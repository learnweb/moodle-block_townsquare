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
namespace block_townsquare\townsquareevents;

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
    
    public function __construct() {
    
    }
    
    /**
     * Function to get events from that are in the calendar for the current user.
     * @return array $events
     */
    function townsquare_get_calendarevents() {
        global $USER;
        
        // Get the courses where the user is enrolled;
        $courses = $this->townsquare_get_courses();
        
        // Get all events from the last six months and the next six months.
        $starttime = time() - 15768000;
        $endtime = time() + 15768000;
        return calendar_get_events($starttime, $endtime, true, true, $courses);
    }
    
    /**
     * Function to get the newest forum posts for the current user;
     * @return false|array
     */
    function townsquare_get_latest_forumposts() {
        global $DB;

        // Check if the forum plugin exists.
        if (!$DB->get_record('modules', ['name' => 'forum'])) {
            return false;
        }
        
        // Save a timespant to get all posts from the last six months.
        $starttime = time() - 15768000;
        
        // Get all course ids from the courses where the current user is enrolled.
        $courses = $this->townsquare_get_courses();
        
        //TODO: valitdate copilot code.
        // Get all posts from the given courses and sort them after the creation time.
        $sql = 'SELECT (ROW_NUMBER() OVER (ORDER BY posts.id)) AS row_num,
                       forum.id AS forumid,
                       posts.id AS postid,
                       posts.discussion AS postdiscussion,
                       posts.parent AS postparent,
                       posts.userid AS postuserid,
                       post.created AS postcreated,
                       post.message AS postmessage
                FROM {forum_posts} posts
                LEFT JOIN {forum_discussions} discuss ON discuss.id = posts.discussion
                LEFT JOIN {forum} forum ON forum.id = discuss.forum
                WHERE forum.course IN (' . implode(';', $courses) . ')
                    AND posts.created > ' . $starttime . '
                ORDER BY posts.created DESC ;';
    }
    
    
    /**
     * Function to get the newest moodleoverflow posts for the current user;
     * @return false|array
     */
    function townsquare_get_latest_moodleoverflowposts() {
        global $DB, $USER;

        // Check if the moodleoverflow plugin exists.
        if (!$DB->get_record('modules', ['name' => 'moodleoverflow'])) {
            return false;
        }
        
        // Save a timestamp to get all posts from the last six months.
        $starttime = time() - 15768000;

        // Get all course ids from the courses where the current user is enrolled.
        $courses = $this->townsquare_get_courses();
        
        // Get all posts from the given courses and sort them after the creation time.
        $sql = 'SELECT (ROW_NUMBER() OVER (ORDER BY posts.id)) AS row_num,
                       moodleoverflow.id AS moodleoverflowid,
                       posts.id AS postid,
                       posts.discussion AS postdiscussion,
                       posts.parent AS postparent,
                       posts.userid AS postuserid,
                       post.created AS postcreated,
                       post.message AS postmessage
                FROM {moodleoverflow_posts} posts
                LEFT JOIN {moodleoverflow_discussions} discuss ON discuss.id = posts.discussion
                LEFT JOIN {moodleoverflow} moodleoverflow ON moodleoverflow.id = discuss.moodleoverflow
                WHERE moodleoverflow.course IN (' . implode(';', $courses) . ')
                    AND posts.created > ' . $starttime . '
                ORDER BY posts.created DESC ;';

        // Get all posts.
        return $DB->get_record_sql($sql);
    }
    
    
    /**
     * Gets the id of all courses where the current user is enrolled
     * @return array
     */
    function townsquare_get_courses() {
        global $USER;
        
        $enrolled_courses = enrol_get_all_users_courses($USER->id);
        $courses = [];
        foreach($enrolled_courses as $enrolled_course) {
            $courses[]=$enrolled_course->id;
        }
        
        return $courses;
    }

}