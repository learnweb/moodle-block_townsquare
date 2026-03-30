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

namespace block_townsquare;

defined('MOODLE_INTERNAL') || die();

use core_plugin_manager;
use context_module;
use moodle_url;
use coding_exception;
use moodle_exception;
use dml_exception;

global $CFG;
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/blocks/townsquare/lib.php');
require_once($CFG->dirroot . '/blocks/townsquare/locallib.php');

/**
 * Class to get relevant events from courses the user is enrolled to.
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
     * @throws coding_exception|dml_exception
     */
    public function __construct() {
        $this->timenow = time();
        $this->timestart = block_townsquare_get_timestart();
        $this->timeend = block_townsquare_get_timeend();
        $this->courses = block_townsquare_get_courses();
    }

    /**
     * Retrieves calendar, post events, merges and sorts them.
     * @return array
     * @throws moodle_exception
     */
    public function get_all_events_sorted(): array {
        global $CFG;
        $coreevents = $this->get_coreevents();
        $postevents = $this->get_postevents();

        // Check if the townsquaresupport plugin is installed.
        $localplugins = core_plugin_manager::instance()->get_plugins_of_type('local');
        $subpluginevents = [];
        if (array_key_exists('townsquaresupport', $localplugins)) {
            require_once($CFG->dirroot . '/local/townsquaresupport/lib.php');
            $subpluginevents = \local_townsquaresupport\local_townsquaresupport_get_subplugin_events();
        }

        // Return the events in a sorted order.
        $events = array_merge($coreevents, $postevents, $subpluginevents);
        return block_townsquare_mergesort($events);
    }

    /**
     * Function to get events/notifications from core plugins for the current user.
     *
     * The events are sorted in descending order by time created (newest event first)
     * @return array
     * @throws dml_exception|coding_exception|moodle_exception
     */
    public function get_coreevents(): array {
        global $DB;

        // Get all events from the database.
        $coreevents = $this->get_events_from_db($this->timestart, $this->timeend, $this->courses);

        // Filter the events and add the instancename.
        foreach ($coreevents as $coreevent) {
            // Filter out events that are not relevant for the user.
            $gradingcap = has_capability('mod/assign:grade', context_module::instance($coreevent->coursemoduleid));
            if (
                townsquare_filter_availability($coreevent) ||
                ($coreevent->modulename == "assign" && ($coreevent->eventtype == "gradingdue" && !$gradingcap)) ||
                ($coreevent->eventtype == "expectcompletionon" && townsquare_filter_activitycompletions($coreevent))
            ) {
                unset($coreevents[$coreevent->id]);
                continue;
            }

            // Add the name of the instance to the event.
            $coreevent->instancename = $DB->get_field($coreevent->modulename, 'name', ['id' => $coreevent->instance]);

            // Modify the content of the event if needed.
            block_townsquare_check_coreevent($coreevent);
        }

        // Filter all assignment events and extract them.
        $assignevents = array_filter($coreevents, fn ($event) => $event->modulename == 'assign');
        $coreevents = array_diff_key($coreevents, $assignevents);

        // Merge the assignments back to the events.
        $assignevents = $this->group_assignments($assignevents);
        return array_values(array_merge($coreevents, $assignevents));
    }

    /**
     * Function to get the newest posts from the forum.
     *
     * The events are sorted in descending order by time created (newest event first)
     * @return array;
     * @throws moodle_exception
     */
    public function get_postevents(): array {
        // Get all post from the database.
        $forumposts = $this->get_forumposts_from_db($this->courses, $this->timestart);

        foreach ($forumposts as $post) {
            if (townsquare_filter_availability($post)) {
                unset($forumposts[$post->row_num]);
            }

            // Add a links and the authors picture.
            $post->linktopost = new moodle_url('/mod/forum/discuss.php', ['d' => $post->postdiscussion], 'p' . $post->postid);
            $post->linktoauthor = new moodle_url('/user/view.php', ['id' => $post->postuserid]);
        }

        return $forumposts;
    }

    // Helper functions.

    /**
     * Searches for posts in the forum or moodleoverflow module.
     * The sql query makes sure that the modules are installed and available.
     * This is a helper function for get_postevents().
     * @param array $courses The ids of the courses where the posts should be searched.
     * @param int $timestart The timestamp from where the posts should be searched.
     * @return array
     * @throws dml_exception|coding_exception
     */
    private function get_forumposts_from_db(array $courses, int $timestart): array {
        global $DB, $USER;
        // Prepare params for sql statement.
        if ($courses == []) {
            return [];
        }
        [$insqlcourses, $inparamscourses] = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED);
        $params = ['courses' => $courses, 'timestart' => $timestart, 'userid' => $USER->id, 'userid2' => $USER->id,
                'userid3' => $USER->id, 'userid4'  => $USER->id, ] + $inparamscourses;

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
                    posts.message AS content,
                    posts.messageformat AS postmessageformat,
                    posts.privatereplyto AS postprivatereplyto,
                    (posts.privatereplyto <> 0 AND posts.userid = :userid3) AS privatereplyfrom,
                    (posts.privatereplyto <> 0 AND posts.privatereplyto = :userid4) AS privatereplyto
                FROM {forum_posts} posts
                JOIN {forum_discussions} discuss ON discuss.id = posts.discussion
                JOIN {forum} module ON module.id = discuss.forum
                JOIN {modules} modules ON modules.name = 'forum'
                JOIN {user} u ON u.id = posts.userid
                JOIN {course_modules} cm ON (cm.course = module.course AND cm.module = modules.id AND cm.instance = module.id)
                WHERE discuss.course $insqlcourses
                    AND posts.created > :timestart
                    AND cm.visible = 1
                    AND modules.visible = 1
                    AND ( posts.privatereplyto = 0 OR posts.userid = :userid OR posts.privatereplyto = :userid2)
                ORDER BY posts.created DESC;";

        // Get all posts.
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Searches for events in the events table, that are relevant to the timeline.
     * This is a helper function for get_coreevents().
     * @param int $timestart The time from where the events should be searched. Not equal to timestart in the database events table.
     * @param int $timeend The time until where the events should be searched.
     * @param array $courses The ids of the courses where the events should be searched.
     * @return array
     * @throws dml_exception
     * @throws coding_exception
     */
    private function get_events_from_db(int $timestart, int $timeend, array $courses): array {
        global $DB;

        // As there are no events without courses, return an empty array.
        if ($courses == []) {
            return [];
        }

        // Due to compatability reasons, only events from core modules are shown.
        $modules = ['assign', 'book', 'chat', 'choice', 'data', 'feedback', 'file', 'folder', 'forum', 'glossary', 'h5pactivity',
                    'imscp', 'label', 'lesson', 'lti', 'page', 'quiz', 'resource', 'scorm', 'survey', 'url', 'wiki', 'workshop', ];

        // Prepare params for sql statement.
        [$insqlcourses, $inparamscourses] = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED);
        [$insqlmodules, $inparamsmodules] = $DB->get_in_or_equal($modules, SQL_PARAMS_NAMED);
        $params = ['timestart' => $timestart, 'timeduration' => $timestart, 'timeend' => $timeend, 'timenow' => $this->timenow,
                'timenow2' => $this->timenow, ] + $inparamscourses + $inparamsmodules;

        // Set the sql statement.
        $sql = "SELECT e.id, e.name AS content, e.courseid, cm.id AS coursemoduleid, cm.availability AS availability,
                e.groupid, e.userid, e.modulename, e.instance, e.eventtype, e.timestart, e.timemodified, e.visible
                FROM {event} e
                JOIN {modules} m ON e.modulename = m.name
                JOIN {course_modules} cm ON (cm.course = e.courseid AND cm.module = m.id AND cm.instance = e.instance)
                LEFT JOIN {assign} assign ON (e.modulename = 'assign' AND assign.id = e.instance)
                WHERE (e.timestart >= :timestart OR e.timestart+e.timeduration > :timeduration)
                  AND e.timestart <= :timeend
                  AND e.courseid $insqlcourses
                  AND e.modulename $insqlmodules
                  AND m.visible = 1
                  AND (e.name NOT LIKE '" . '0' . "' AND e.eventtype NOT LIKE '" . '0' . "' )
                  AND (e.instance <> 0 AND e.visible = 1)
                  AND (e.modulename != 'assign'
                    OR (:timenow < (e.timestart + 604800) AND :timenow2 >= assign.allowsubmissionsfromdate )
                  )
                ORDER BY e.timestart DESC";
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Checks if there are assignment groups. A group exists if many assignemnts from one course have the same due date.
     * This is often the case when exercises are split up (like: week 1 - exercise 1, week 1 -  exercise. 2,...)
     * @param array $assignevents
     * @return array All events
     */
    private function group_assignments(array $assignevents): array {
        global $DB;
        $groupedevents = [];
        // Put each event in a group dependent on the course->eventtype->timestart.
        foreach ($assignevents as $event) {
            // Create a complex key where only events that should be one event are stored in the same key.
            $groupedevents["{$event->courseid}-{$event->eventtype}-{$event->timestart}"][] = $event;
        }

        // Now group together.
        $assignevents = [];
        foreach ($groupedevents as $events) {
            if (count($events) > 1) {
                // All events should make one. Take the first as reference and update what needs to be updated.
                $subinstances = [];
                foreach ($events as $event) {
                    $link = (new moodle_url("/mod/{$event->modulename}/view.php", ['id' => $event->coursemoduleid]))->out();
                    $subinstances[] = [
                        "instancename" => $DB->get_field($event->modulename, 'name', ['id' => $event->instance]),
                        "linktoactivity" => $link,
                        "dueevent" => $event->eventtype == "due",
                    ];
                }
                // Sort subinstances by instance name to maintain consistent order.
                usort($subinstances, function ($a, $b) {
                    return strcmp($a['instancename'], $b['instancename']);
                });

                $assignevent = reset($events);
                $assignevent->subinstances = $subinstances;
                $assignevents[] = $assignevent;
            } else {
                $assignevents[] = reset($events);
            }
        }
        return $assignevents;
    }
}
