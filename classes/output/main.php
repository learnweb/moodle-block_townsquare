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

use core\output\named_templatable;
use core\output\renderable;
use core\output\renderer_base;

/**
 * The Town Square block: the filter bar and the content.
 *
 * @package    block_townsquare
 * @copyright  2023 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements named_templatable, renderable {
    #[\Override]
    public function get_template_name(renderer_base $renderer): string {
        return 'block_townsquare/main';
    }

    #[\Override]
    public function export_for_template(renderer_base $output): array {
        return (new content())->export_for_template($output) + ['timespans' => $this->get_timespans()];
    }

    /**
     * Returns the time spans of the time filter. A time span covers the same time before and after now.
     *
     * @return array
     */
    private function get_timespans(): array {
        $timespans = ['twodays' => 2 * DAYSECS, 'fivedays' => 5 * DAYSECS, 'week' => WEEKSECS, 'month' => 30 * DAYSECS];
        $result = [];
        foreach ($timespans as $name => $seconds) {
            $result[] = [
                'id' => "ts_time_{$name}",
                'timespan' => $seconds,
                'label' => get_string("timespan_{$name}", 'block_townsquare'),
            ];
        }
        return $result;
    }
}
