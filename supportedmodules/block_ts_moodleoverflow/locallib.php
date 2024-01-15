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
 * Internal library of functions for the ts_moodleoverflow subplugin
 *
 * @package block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
require_once($CFG->dirroot . '/blocks/townsquare/locallib.php');

/**
 * Function to get the newest post from the moodleoverflow module.
 * @param array $courses    The courses that will searched.
 * @param int $timestart    The start time of the search.
 * @param int $timeend      The end time of the search.
 * @return array
 */
function ts_moodleoverflow_get_events($courses, $timestart): array {
    global $DB;

    // If moodleoverflow is not installed or not activated, return empty array.
    if (!$DB->get_record('modules', ['name' => 'forum', 'visible' => 1])) {
        return [];
    }

    // Get posts from the database.
    $posts = ts_moodleoverflow_get_post_from_db($courses, $timestart);

    // Filter posts by availability.
    foreach($posts as $post) {
        if (townsquare_filter_availability($post)) {
            unset($posts[$post->row_num]);
        }
    }

    return $posts;
}

function ts_moodleoverflow_get_post_from_db($courses, $timestart): array {
    global $DB;
    // Prepare params for sql statement.
    list($insqlcourses, $inparamscourses) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED);
    $params = ['courses' => $courses, 'timestart' => $timestart] + $inparamscourses;

    $sql = "SELECT (ROW_NUMBER() OVER (ORDER BY posts.id)) AS row_num,
                'moodleoverflow' AS modulename, 
                module.id AS instanceid,
                module.anonymous AS anonymoussetting,  
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
                posts.message AS postmessage
            FROM {moodleoverflow_posts} posts
            JOIN {moodleoverflow_discussions} discuss ON discuss.id = posts.discussion
            JOIN {moodleoverflow} module ON module.id = discuss.moodleoverflow
            JOIN {modules} modules ON modules.name = 'moodleoverflow'
            JOIN {user} u ON u.id = posts.userid
            JOIN {course_modules} cm ON (cm.course = module.course AND cm.module = modules.id AND cm.instance = module.id)
            WHERE discuss.course $insqlcourses
                AND posts.created > :timestart
                AND cm.visible = 1
                AND modules.visible = 1
                ORDER BY posts.created DESC;";

    return $DB->get_records_sql($sql, $params);
}

