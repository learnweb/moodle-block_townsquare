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

namespace block_townsquare\hook;

use block_townsquare\item\deadline_item;
use block_townsquare\item\post_item;

/**
 * Hook for plugins to show their posts and deadlines in Town Square.
 *
 * Town Square only shows items of activities in the given courses that the user can see, posts after timestart and
 * deadlines between timestart and timeend. Everything else (capabilities, groups, anonymity, ...) is checked by the
 * plugin that adds the items.
 *
 * @package     block_townsquare
 * @copyright   2026 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\attribute\label('Allows plugins to show their posts and deadlines in the Town Square block.')]
#[\core\attribute\tags('block_townsquare')]
final class extend_content {
    /** @var post_item[] The posts added by plugins */
    private array $posts = [];

    /** @var deadline_item[] The deadlines added by plugins */
    private array $deadlines = [];

    /**
     * Constructor.
     *
     * @param int $userid The user who sees Town Square
     * @param int[] $courseids The courses of the user
     * @param int $timestart Items before this time are not shown
     * @param int $timeend Deadlines after this time are not shown
     */
    public function __construct(
        public readonly int $userid,
        public readonly array $courseids,
        public readonly int $timestart,
        public readonly int $timeend,
    ) {
    }

    /**
     * Adds a post, it is shown in the "Posts" tab.
     * @param post_item $post
     */
    public function add_post(post_item $post): void {
        $this->posts[] = $post;
    }

    /**
     * Adds a deadline, it is shown in the "Deadlines" tab.
     * @param deadline_item $deadline
     */
    public function add_deadline(deadline_item $deadline): void {
        $this->deadlines[] = $deadline;
    }

    /**
     * Returns the added posts.
     * @return post_item[]
     */
    public function get_posts(): array {
        return $this->posts;
    }

    /**
     * Returns the added deadlines.
     * @return deadline_item[]
     */
    public function get_deadlines(): array {
        return $this->deadlines;
    }
}
