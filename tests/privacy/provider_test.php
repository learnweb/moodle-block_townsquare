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
 * Block recentlyaccesseditems privacy provider tests.
 *
 * @package    block_recentlyaccesseditems
 * @copyright  2018 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.6
 */
namespace block_townsquare\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use block_townsquare\privacy\provider;
use core_privacy\tests\provider_testcase;

class provider_test extends provider_testcase {
    // TODO: implement privacy provider tests.
    public function test_provider(): void {
    }

    // Functions that needs to be implemented.
    // public function test_get_contexts_for_userid() {} // Test getting the context for the user id related to this plugin.
    // public function test_get_users_in_context() {}  // Test getting the users in the context related to this plugin.

    // public function test_get_metadata() {}
    // public function test_export_user_data() {}
    // public function test_delete_data_for_all_users_in_context() {}
    // public function test_delete_data_for_user() {}
    // public function test_delete_data_for_users() {}
}
