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

use moodle_url;

/**
 * A post that is shown in the "Posts" tab of Town Square.
 *
 * The module of the post provides everything that is shown: the formatted message, the name, link and picture of the
 * author. Town Square shows it as it is, so the module decides e.g. how anonymous authors are shown.
 *
 * @package     block_townsquare
 * @copyright   2026 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class post_item {
    /**
     * Constructor.
     *
     * @param int $courseid Id of the course of the activity
     * @param int $cmid Course module id of the activity
     * @param string $discussionsubject Subject of the discussion of the post (plain text)
     * @param string $message Message of the post, formatted with format_text() in the context of the module (HTML)
     * @param int $time Timestamp when the post was written
     * @param moodle_url $url Link to the post
     * @param string $authorname Name of the author as it should be shown (plain text)
     * @param int|null $authorid Id of the author, null if the author is hidden (e.g. anonymous)
     * @param moodle_url|null $authorurl Link to the author, null to show the name without a link
     * @param moodle_url|null $pictureurl Picture of the author, null to show the default user picture
     * @param int $privatereplyto Id of the user the post is a private reply to, 0 if it is not a private reply
     */
    public function __construct(
        public readonly int $courseid,
        public readonly int $cmid,
        public readonly string $discussionsubject,
        public readonly string $message,
        public readonly int $time,
        public readonly moodle_url $url,
        public readonly string $authorname,
        public readonly ?int $authorid = null,
        public readonly ?moodle_url $authorurl = null,
        public readonly ?moodle_url $pictureurl = null,
        public readonly int $privatereplyto = 0,
    ) {
    }
}
