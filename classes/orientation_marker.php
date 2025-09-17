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

/**
 * Class to show information to the user
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_townsquare;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/townsquare/locallib.php');

/**
 * Class that represent a orientation marker.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orientation_marker {
    // Attributes.

    /** @var int an ID to identify every content in townsquare */
    private int $contentid;

    /** @var int The timestamp of the current day. */
    private int $today;

    /** @var bool variable for the mustache template */
    public bool $isorientationmarker = true;

    // Constructor.

    /**
     * Constructor for a letter
     *
     * @param int $contentid The ID to identify the orientation marker
     * @param int $time      A Timestamp of the time that the orientation marker is created
     */
    public function __construct($contentid, $time) {
        $this->contentid = $contentid;
        $this->today = $time;
    }

    // Functions.

    /**
     * Export function for the mustache template.
     * @return array
     */
    public function export_data(): array {
        // Change the timestamp to a date.
        $date = date('d.m.Y', $this->today);

        return [
            'contentid' => $this->contentid,
            'date' => $date,
            'isorientationmarker' => $this->isorientationmarker,
            'orientationmarkercolor' => townsquare_get_colorsetting('orientationmarker'),
        ];
    }
}
