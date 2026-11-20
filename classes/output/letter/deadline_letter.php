<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace block_townsquare\output\letter;

use block_townsquare\item\deadline_item;
use core\output\renderer_base;

/**
 * A letter for deadlines.
 *
 * It shows one deadline or groups several deadlines of the same course and module at the same time, e.g. exercises
 * that are split up (week 1 - exercise 1, week 1 - exercise 2, ...).
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deadline_letter extends letter {
    /** @var deadline_item[] The deadlines of the letter, sorted by their name */
    private array $deadlines;

    /**
     * Constructor of the deadline letter.
     *
     * @param int $contentid internal ID in the townsquare block
     * @param deadline_item[] $deadlines One deadline or several deadlines of the same course and module at the same time
     */
    public function __construct(int $contentid, array $deadlines) {
        usort($deadlines, fn($a, $b) => strcmp($a->name, $b->name));
        parent::__construct($contentid, $deadlines[0]->courseid, $deadlines[0]->cmid, $deadlines[0]->time);
        $this->deadlines = $deadlines;
    }

    #[\Override]
    public function get_template_name(renderer_base $renderer): string {
        return 'block_townsquare/letter/deadline_letter';
    }

    #[\Override]
    public function export_for_template(renderer_base $output): array {
        $grouped = count($this->deadlines) > 1;
        return $this->export_basics($output) + [
            'content' => $this->deadlines[0]->name,
            'grouped' => $grouped,
            'deadlines' => !$grouped ? [] : array_map(fn($deadline) => [
                'name' => $deadline->name,
                'linktoactivity' => $this->get_activityurl($deadline->cmid),
            ], $this->deadlines),
        ];
    }
}
