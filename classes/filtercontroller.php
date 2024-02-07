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

/**
 * Filter Controller of block_townsquare
 *
 * @package   block_townsquare
 * @copyright 2024 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_townsquare;

/**
 * Filter Controller Class.
 *
 * This Class controls the filter settings that user sets for the townsquare.
 * The main functionality are:
 * - store the filter settings that the user sets in the database
 * - retrieve filter settings from the database and apply them when the user loads the townsquare
 */
class filtercontroller {

    /** @var array usersettings that the current user set */
    public array $usersettings;

    /** @var array timefilter that the user set */
    public array $timefilter;

    /** @var array letterfilter that the user set */
    public array $letterfilter;

    public function __construct() {
        global $DB, $USER;

        // Retrieve the user filter settings from the database. If there are no settings, store a empty array.
        if (!$this->usersettings = $DB->get_record('townsquare_usersettings', ['userid' => $USER->id])) {
            $this->usersettings = [];
        }

        // Build an array for the mustache templates to use.
    }

    // Store functions.
    public function store_usersettings(array $usersettings) {
        // Change the information from the array to a number that can store the database.
        // Check if the user already has settings in the database.
        // If yes,update the settings.
        // If no, insert the settings (on the GUI is a "submit" button, that also has a helpicon).
    }

    // Retrieve functions.
    public function set_timefilter(): array {
        if ($this->usersettings == []) {
            return [];
        }

        // Set the default.
        $this->timefilter = [
            'all' => true,
            'nextweek' => false,
            'nextmonth' => false,
            'lastweek' => false,
            'lastmonth' => false,
        ];

        // Change the setting according to the database (case 0 is the default).
        switch($this->usersettings->timefilter) {
            case 1:
                $this->timefilter['all'] = false;
                $this->timefilter['nextweek'] = true;
                break;
            case 2:
                $this->timefilter['all'] = false;
                $this->timefilter['nextmonth'] = true;
                break;
            case 3:
                $this->timefilter['all'] = false;
                $this->timefilter['lastweek'] = true;
                break;
            case 4:
                $this->timefilter['all'] = false;
                $this->timefilter['lastmonth'] = true;
                break;
            default:
                break;
        }

        return $this->timefilter;
    }

    public function set_letterfilter(): array {
        if ($this->usersettings == []) {
            return [];
        }

        switch($this->usersettings->letterfilter) {
            case 0:
                $this->letterfilter['basicletter'] = true;
                $this->letterfilter['completionletter'] = true;
                $this->letterfilter['postletter'] = true;
                break;
            case 1:
                $this->letterfilter['basicletter'] = true;
                $this->letterfilter['completionletter'] = true;
                $this->letterfilter['postletter'] = false;
                break;
            case 2:
                $this->letterfilter['basicletter'] = true;
                $this->letterfilter['completionletter'] = false;
                $this->letterfilter['postletter'] = true;
                break;
            case 3:
                $this->letterfilter['basicletter'] = true;
                $this->letterfilter['completionletter'] = false;
                $this->letterfilter['postletter'] = false;
                break;
            case 4:
                $this->letterfilter['basicletter'] = false;
                $this->letterfilter['completionletter'] = true;
                $this->letterfilter['postletter'] = true;
                break;
            case 5:
                $this->letterfilter['basicletter'] = false;
                $this->letterfilter['completionletter'] = true;
                $this->letterfilter['postletter'] = false;
                break;
            case 6:
                $this->letterfilter['basicletter'] = false;
                $this->letterfilter['completionletter'] = false;
                $this->letterfilter['postletter'] = true;
                break;
            case 7:
                $this->letterfilter['basicletter'] = false;
                $this->letterfilter['completionletter'] = false;
                $this->letterfilter['postletter'] = false;
                break;
            default:
                break;
        }
        return $this->letterfilter;
    }

}
