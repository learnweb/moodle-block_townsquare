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

namespace block_townsquare\local\service;

use block_townsquare\hook\extend_content;
use block_townsquare\item\deadline_item;
use block_townsquare\item\post_item;
use block_townsquare\local\source\calendar_deadlines;
use block_townsquare\local\source\forum_posts;
use cache;

/**
 * Service for the items (posts and deadlines) that Town Square shows to the current user.
 *
 * The items come from the sources of the core modules and from other plugins (hook extend_content). Only items of
 * activities the user can see in the courses of the user and in the time span are shown. The items are cached in the
 * session for 30 minutes.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class items {
    /** @var int How long the items are cached, in seconds */
    private const CACHE_TTL = 30 * MINSECS;

    /** @var int[] The ids of the courses of the user */
    public readonly array $courseids;

    /** @var int Posts before this time are not shown, deadlines from this time on are shown */
    public readonly int $timestart;

    /** @var int Deadlines until this time are shown */
    public readonly int $timeend;

    /** @var array|null The cached items with the time of their last update */
    private ?array $cached = null;

    /**
     * Constructor. The time span is set by the admin (setting "timespan").
     */
    public function __construct() {
        global $USER;
        $this->courseids = array_keys(enrol_get_all_users_courses($USER->id, true));
        $timespan = (int) get_config('block_townsquare', 'timespan');
        $this->timestart = time() - $timespan;
        $this->timeend = time() + $timespan;
    }

    /**
     * Returns the items for the user, the latest first.
     * @return array deadline_item and post_item objects
     */
    public function get(): array {
        return $this->load()['items'];
    }

    /**
     * Returns the time when the items were collected.
     * @return int
     */
    public function get_lastupdate(): int {
        return $this->load()['lastupdate'];
    }

    /**
     * Deletes the cached items, so they are collected again.
     */
    public static function purge_cache(): void {
        cache::make('block_townsquare', 'townsquareevents')->delete('items');
    }

    /**
     * Returns the items with the time of their last update, from the cache if it is up to date.
     * @return array ['items' => array, 'lastupdate' => int]
     */
    private function load(): array {
        if ($this->cached) {
            return $this->cached;
        }

        $cache = cache::make('block_townsquare', 'townsquareevents');
        $cached = $cache->get('items');
        if ($cached && $cached['lastupdate'] >= time() - self::CACHE_TTL) {
            // Activities can be deleted or hidden while the items are cached.
            $cached['items'] = array_values(array_filter($cached['items'], fn($item) => $this->is_shown($item)));
            $this->cached = $cached;
            return $this->cached;
        }

        $this->cached = ['items' => $this->collect(), 'lastupdate' => time()];
        $cache->set('items', $this->cached);
        return $this->cached;
    }

    /**
     * Collects the items of the core modules and of other plugins.
     * @return array deadline_item and post_item objects, the latest first
     */
    private function collect(): array {
        global $USER;
        $hook = new extend_content($USER->id, $this->courseids, $this->timestart, $this->timeend);
        calendar_deadlines::extend_content($hook);
        forum_posts::extend_content($hook);
        \core\di::get(\core\hook\manager::class)->dispatch($hook);

        // Sort the items, the latest first. The order of items with the same time is kept.
        $items = array_filter(array_merge($hook->get_deadlines(), $hook->get_posts()), fn($item) => $this->is_shown($item));
        usort($items, fn($a, $b) => $b->time <=> $a->time);
        return $items;
    }

    /**
     * Checks if an item is shown: its activity is in a course of the user and the user can see it (visible and
     * available), and the item is in the time span.
     * @param deadline_item|post_item $item
     * @return bool
     */
    private function is_shown(deadline_item|post_item $item): bool {
        $intimespan = $item instanceof post_item
            ? $item->time > $this->timestart
            : $item->time >= $this->timestart && $item->time <= $this->timeend;
        if (!$intimespan || !in_array($item->courseid, $this->courseids)) {
            return false;
        }
        $cm = get_fast_modinfo($item->courseid)->get_cms()[$item->cmid] ?? null;
        return $cm && $cm->uservisible;
    }
}
