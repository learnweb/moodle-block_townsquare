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

// Import other namespaces.

/**
 * Abstract Class to show the current date
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orientation_marker {
    // Attributes.

    /** @var int an ID to identify every content in townsquare */
    private $contentid;

    /** @var int The timestamp of todays day. */
    private $today;

    /** @var object the current user */
    private $username;

    /** @var bool variable for the mustache template */
    public $isorientationmarker = true;

    // Constructor.

    /**
     * Constructor for a letter
     *
     */
    public function __construct($contentid, $time) {
        global $USER;
        $this->contentid = $contentid;
        $this->username = $USER->firstname . " " . $USER->lastname;
        $this->today = $time;

    }

    /**
     * Export function for the mustache template.
     * @return array
     */
    public function export_data() {
        // Change the timestamp to a date.
        $date = date('d.m.Y', $this->today);

        return [
            'contentid' => $this->contentid,
            'username' => $this->username,
            'date' => $date,
            'isorientationmarker' => $this->isorientationmarker,
        ];
    }

}
