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

namespace block_townsquare\output;

use block_townsquare\output\letter\letter;
use core\output\named_templatable;
use core\output\renderable;
use core\output\renderer_base;

/**
 * A group of letters: the letters of one day in the timezone of the user.
 *
 * @package   block_townsquare
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lettergroup implements named_templatable, renderable {
    /**
     * Constructor.
     *
     * @param int $daystart Midnight of the day in the timezone of the user
     * @param letter[] $letters The letters of the day, in the order they are shown
     * @param bool $firstpast If it is the first past group, the heading "Past" is shown before it
     */
    public function __construct(
        private int $daystart,
        private array $letters,
        private bool $firstpast = false,
    ) {
    }

    #[\Override]
    public function get_template_name(renderer_base $renderer): string {
        return 'block_townsquare/lettergroup';
    }

    #[\Override]
    public function export_for_template(renderer_base $output): array {
        return [
            'day' => userdate($this->daystart, '%d.%m.%Y', 99, false),
            'daylabel' => $this->get_daylabel(),
            'daydate' => userdate($this->daystart, get_string('strftimedayshort', 'langconfig')),
            'count' => count($this->letters),
            'ispast' => $this->is_past(),
            'firstpast' => $this->firstpast,
            'letters' => array_map(fn($letter) => ['letter' => $output->render($letter)], $this->letters),
        ];
    }

    /**
     * Returns midnight of the day of the group in the timezone of the user.
     * @return int
     */
    public function get_daystart(): int {
        return $this->daystart;
    }

    /**
     * Returns the letters of the group, in the order they are shown.
     * @return letter[]
     */
    public function get_letters(): array {
        return $this->letters;
    }

    /**
     * Returns if the day of the group is before today.
     * @return bool
     */
    public function is_past(): bool {
        return $this->daystart < usergetmidnight(time());
    }

    /**
     * Returns "Today", "Tomorrow" or "Yesterday" for the day of the group, otherwise an empty string.
     * @return string
     */
    private function get_daylabel(): string {
        // A day has 23 to 25 hours (daylight saving time), so the days next to today are found from its midnight.
        $today = usergetmidnight(time());
        return match ($this->daystart) {
            $today => get_string('today', 'calendar'),
            usergetmidnight($today + DAYSECS + 2 * HOURSECS) => get_string('tomorrow', 'calendar'),
            usergetmidnight($today - 2 * HOURSECS) => get_string('yesterday', 'calendar'),
            default => '',
        };
    }
}
