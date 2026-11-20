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
namespace block_townsquare\local\source;

use block_townsquare\hook\extend_content;
use block_townsquare\item\deadline_item;
use context_module;

/**
 * Source for the deadlines of the core modules: their calendar events, e.g. due dates and expected completions.
 *
 * It works like a callback of the hook extend_content and is called by \block_townsquare\local\service\items.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calendar_deadlines {
    /**
     * Adds the deadlines of the calendar events that are relevant for the user.
     * @param extend_content $hook
     */
    public static function extend_content(extend_content $hook): void {
        foreach (self::get_events_from_db($hook) as $event) {
            // Filter out events that are not relevant for the user.
            $context = context_module::instance($event->coursemoduleid);
            if (
                ($event->modulename == 'assign' && $event->eventtype == 'gradingdue' &&
                    !has_capability('mod/assign:grade', $context, $hook->userid)) ||
                ($event->eventtype == 'expectcompletionon' && self::is_completed($event->coursemoduleid, $hook->userid))
            ) {
                continue;
            }

            $hook->add_deadline(new deadline_item(
                courseid: $event->courseid,
                cmid: $event->coursemoduleid,
                name: $event->content,
                time: $event->timestart,
            ));
        }
    }

    /**
     * Checks if the user has already completed an activity.
     * @param int $cmid
     * @param int $userid
     * @return bool
     */
    private static function is_completed(int $cmid, int $userid): bool {
        global $DB;
        $params = ['coursemoduleid' => $cmid, 'userid' => $userid];
        return !empty($DB->get_field('course_modules_completion', 'completionstate', $params));
    }

    /**
     * Searches for events in the events table, that are relevant to the timeline.
     * @param extend_content $hook The courses and the time span to search in
     * @return array
     */
    private static function get_events_from_db(extend_content $hook): array {
        global $DB;

        // As there are no events without courses, return an empty array.
        if ($hook->courseids == []) {
            return [];
        }

        // Due to compatability reasons, only events from core modules are shown.
        $modules = ['assign', 'book', 'chat', 'choice', 'data', 'feedback', 'file', 'folder', 'forum', 'glossary', 'h5pactivity',
                    'imscp', 'label', 'lesson', 'lti', 'page', 'quiz', 'resource', 'scorm', 'survey', 'url', 'wiki', 'workshop', ];

        // Prepare params for sql statement.
        $timenow = time();
        [$insqlcourses, $inparamscourses] = $DB->get_in_or_equal($hook->courseids, SQL_PARAMS_NAMED);
        [$insqlmodules, $inparamsmodules] = $DB->get_in_or_equal($modules, SQL_PARAMS_NAMED);
        $params = ['timestart' => $hook->timestart, 'timeduration' => $hook->timestart, 'timeend' => $hook->timeend,
                'timenow' => $timenow, 'timenow2' => $timenow, ] + $inparamscourses + $inparamsmodules;

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
