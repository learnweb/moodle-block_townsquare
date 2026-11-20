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

namespace block_townsquare\item;

/**
 * A deadline that is shown in the "Deadlines" tab of Town Square, e.g. "Exercise sheet 5 is due".
 *
 * Deadlines of the same course and module at the same time are shown together in one letter.
 *
 * @package     block_townsquare
 * @copyright   2026 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class deadline_item {
    /**
     * Constructor.
     *
     * @param int $courseid Id of the course of the activity
     * @param int $cmid Course module id of the activity
     * @param string $name What happens at the deadline, e.g. "Exercise sheet 5 is due" (plain text)
     * @param int $time Timestamp of the deadline
     */
    public function __construct(
        public readonly int $courseid,
        public readonly int $cmid,
        public readonly string $name,
        public readonly int $time,
    ) {
    }
}
