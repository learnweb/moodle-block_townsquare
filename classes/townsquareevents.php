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

use block_townsquare\hook\extend_content;
use block_townsquare\item\deadline_item;
use block_townsquare\item\post_item;
use cache;
use context_module;
use core_user\fields;
use moodle_url;
use coding_exception;
use moodle_exception;
use dml_exception;
use user_picture;

global $CFG;
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/blocks/townsquare/lib.php');

/**
 * Class to get the items (deadlines and posts) from courses the user is enrolled to.
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
     * Retrieves the deadlines and posts, the latest first.
     * @return array deadline_item and post_item objects
     * @throws moodle_exception
     */
    public function get_items(): array {
        // Check if the cache already has data and if the data is still considered up to date.
        $cache = cache::make('block_townsquare', 'townsquareevents');
        $cached = $cache->get('items');
        if ($cached && $cached["lastupdate"] >= time() - BLOCK_TOWNSQUARE_CACHE_TTL) {
            // Activities can be deleted or hidden while the items are cached.
            return array_values(array_filter($cached["items"], fn($item) => $this->is_visible($item->courseid, $item->cmid)));
        }

        // Sort the items, the latest first. The order of items with the same time is kept.
        $items = array_merge($this->get_deadlines(), $this->get_posts(), $this->get_hook_items());
        usort($items, fn($a, $b) => $b->time <=> $a->time);

        // Set the cache.
        $cache->set('items', ['items' => $items, "lastupdate" => time()]);
        return $items;
    }

    /**
     * Function to get the deadlines from the calendar events of core plugins for the current user.
     *
     * @return deadline_item[]
     * @throws dml_exception|coding_exception|moodle_exception
     */
    public function get_deadlines(): array {
        $deadlines = [];
        foreach ($this->get_events_from_db($this->timestart, $this->timeend, $this->courses) as $event) {
            // Filter out events that are not relevant for the user.
            $gradingcap = has_capability('mod/assign:grade', context_module::instance($event->coursemoduleid));
            if (
                !$this->is_visible($event->courseid, $event->coursemoduleid) ||
                ($event->modulename == "assign" && ($event->eventtype == "gradingdue" && !$gradingcap)) ||
                ($event->eventtype == "expectcompletionon" && townsquare_filter_activitycompletions($event))
            ) {
                continue;
            }

            $deadlines[] = new deadline_item(
                courseid: $event->courseid,
                cmid: $event->coursemoduleid,
                name: $event->content,
                time: $event->timestart,
            );
        }
        return $deadlines;
    }

    /**
     * Function to get the newest posts from the forum.
     *
     * @return post_item[]
     * @throws moodle_exception
     */
    public function get_posts(): array {
        global $PAGE;

        $posts = [];
        foreach ($this->get_forumposts_from_db($this->courses, $this->timestart) as $post) {
            if (!$this->is_visible($post->courseid, $post->coursemoduleid)) {
                continue;
            }

            // Format the message.
            $context = context_module::instance($post->coursemoduleid);
            $message = file_rewrite_pluginfile_urls(
                $post->message,
                'pluginfile.php',
                $context->id,
                'mod_forum',
                'post',
                $post->id,
                ['includetoken' => true]
            );
            $message = format_text($message, $post->messageformat, ['para' => true, 'context' => $context]);

            // The author with its picture.
            $author = user_picture::unalias($post, null, 'authorid', 'author');
            $picture = new user_picture($author);
            $picture->size = 35;

            $posts[] = new post_item(
                courseid: $post->courseid,
                cmid: $post->coursemoduleid,
                discussionsubject: $post->discussionsubject,
                message: $message,
                time: $post->created,
                url: new moodle_url('/mod/forum/discuss.php', ['d' => $post->discussion], 'p' . $post->id),
                authorname: fullname($author),
                authorid: $author->id,
                authorurl: new moodle_url('/user/view.php', ['id' => $author->id]),
                pictureurl: $picture->get_url($PAGE),
                privatereplyto: $post->privatereplyto,
            );
        }
        return $posts;
    }

    /**
     * Collects the posts and deadlines of other plugins (see \block_townsquare\hook\extend_content).
     * Only items of visible activities in the shown courses and in the time span are used.
     *
     * @return array deadline_item and post_item objects
     * @throws moodle_exception
     */
    public function get_hook_items(): array {
        global $USER;
        $hook = new extend_content($USER->id, $this->courses, $this->timestart, $this->timeend);
        \core\di::get(\core\hook\manager::class)->dispatch($hook);

        $posts = array_filter($hook->get_posts(), fn($post) => $post->time > $this->timestart);
        $deadlines = array_filter(
            $hook->get_deadlines(),
            fn($deadline) => $deadline->time >= $this->timestart && $deadline->time <= $this->timeend
        );
        return array_values(array_filter(
            array_merge($posts, $deadlines),
            fn($item) => $this->is_visible($item->courseid, $item->cmid)
        ));
    }

    // Helper functions.

    /**
     * Checks if the user can see an activity of a shown course (it is visible and available to the user).
     * @param int $courseid
     * @param int $cmid
     * @return bool
     * @throws moodle_exception
     */
    private function is_visible(int $courseid, int $cmid): bool {
        if (!in_array($courseid, $this->courses)) {
            return false;
        }
        $cm = get_fast_modinfo($courseid)->get_cms()[$cmid] ?? null;
        return $cm && $cm->uservisible;
    }

    /**
     * Searches for posts in the forum module.
     * The sql query makes sure that the modules are installed and available.
     * This is a helper function for get_posts().
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
        $params = ['timestart' => $timestart, 'userid' => $USER->id, 'userid2' => $USER->id] + $inparamscourses;
        $authorfields = fields::for_userpic()->get_sql('u', false, 'author', 'authorid')->selects;

        $sql = "SELECT posts.id,
                    cm.id AS coursemoduleid,
                    discuss.course AS courseid,
                    discuss.name AS discussionsubject,
                    posts.discussion,
                    posts.created,
                    posts.message,
                    posts.messageformat,
                    posts.privatereplyto
                    $authorfields
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
     * This is a helper function for get_deadlines().
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
        $sql = "SELECT e.id, e.name AS content, e.courseid, cm.id AS coursemoduleid,
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
}
