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

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use block_townsquare\privacy\provider;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;

class provider_test extends provider_testcase {

    // Attributes.
    private $testdata;

    public function setUp(): void {
        // Create a course and a user.
        $this->testdata = new \stdClass();
        $this->testdata->course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $this->testdata->student = $this->getDataGenerator()->create_user();
        $this->testdata->studentcontext = \context_user::instance($this->testdata->student->id);
        $this->getDataGenerator()->enrol_user($this->testdata->student->id, $this->testdata->course->id, 'student');
        $this->resetAfterTest();
    }

    // public function tearDown(): void {}

    // Functions that needs to be implemented.

    /**
     * Test if the provider gets the right context for a user.
     * @return void
     */
    public function test_get_contexts_for_userid(): void {
        // Check that no context are found before the setting is set.
        $contextlist = provider::get_contexts_for_userid($this->testdata->student->id);
        $this->assertEquals(0, count($contextlist));

        // Add a usersetting.
        $this->helper_add_preference($this->testdata->student->id);
        $contextlist = provider::get_contexts_for_userid($this->testdata->student->id);

        // Check the context that should be there.
        $this->assertEquals(1, count($contextlist));
        $this->assertEquals($this->testdata->studentcontext, $contextlist->current());
    }

    /**
     * Test getting the users in the context related to this plugin.
     * @return void
     */
    public function test_get_users_in_context():void {
        // Check that no users are found in the context before the setting is set.
        $userlist = new userlist($this->testdata->studentcontext, 'block_townsquare');
        provider::get_users_in_context($userlist);
        $this->assertEquals(0 , count($userlist));

        // Add a usersetting.
        $this->helper_add_preference($this->testdata->student->id);
        provider::get_users_in_context($userlist);

        // Check that the provider fetches the right data.
        $this->assertEquals(1, count($userlist));
        $this->assertEquals($this->testdata->student, $userlist->current());
    }


    /**
     * Test if the metadata is correct.
     * @return void
     */
    public function test_get_metadata():void {
        // Get the metadata and check if a collection exists.
        $metadata = provider::get_metadata(new collection('block_townsquare'));
        $collection = $metadata->get_collection();
        $this->assertEquals(1, count($collection));

        // Check the content of the metadata.
        $table = reset($collection);
        $privacyfields = $table->get_privacy_fields();

        $this->assertEquals('block_townsquare', $table->get_name());
        $this->assertEquals(6, count($privacyfields));
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('timefilterpast', $privacyfields);
        $this->assertArrayHasKey('timefilterfuture', $privacyfields);
        $this->assertArrayHasKey('basicletter', $privacyfields);
        $this->assertArrayHasKey('completionletter', $privacyfields);
        $this->assertArrayHasKey('postletter', $privacyfields);
    }

    /**
     * Test if user data get exported correctly.
     * @return void
     */
    public function test_export_user_data() {
        global $DB;
        // Add a usersetting.
        $this->helper_add_preference($this->testdata->student->id);

        // Confirm that the setting is in the database.
        $record = $DB->get_records('block_townsquare_preferences', ['userid' => $this->testdata->student->id]);
        $this->assertEquals(1, count($record));

        // Export the data.
        $approvedlist = new approved_contextlist($this->testdata->student, 'block_townsquare', [$this->testdata->studentcontext->id]);
        provider::export_user_data($approvedlist);
        $writer = writer::with_context($this->testdata->studentcontext);
        $this->assertEquals(true, $writer->has_any_data());
    }

    /**
     * Test if data is delete for all users within an approved contextlist
     * @return void
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;
        // Add another user with a different context and add usersettings.
        $user2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user2->id, $this->testdata->course->id, 'teacher');
        $this->helper_add_preference($this->testdata->student->id);
        $this->helper_add_preference($user2->id);

        // Try a system context deletion, which should have no effect.
        provider::delete_data_for_all_users_in_context(\context_system::instance());
        $this->assertEquals(2, count($DB->get_records('block_townsquare_preferences')));

        // Delete all data in the first users context (studentcontext).
        provider::delete_data_for_all_users_in_context(\context_user::instance($this->testdata->student->id));

        // Only students data should be deleted.
        $records = $DB->get_records('block_townsquare_preferences');
        $this->assertEquals(1, count($records));
    }

    /**
     * Test deleting data within an approved contextlist for a user.
     * @return void
     */
    public function test_delete_data_for_user() {
        global $DB;
        // Add another user with a different context and add usersettings.
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $this->testdata->course->id, 'teacher');
        $this->helper_add_preference($this->testdata->student->id);
        $this->helper_add_preference($teacher->id);

        // Try a system context deletion, which should have no effect.
        provider::delete_data_for_all_users_in_context(\context_system::instance());
        $this->assertEquals(2, count($DB->get_records('block_townsquare_preferences')));

        // Try to delete the teacher data in a students context, which should have no effect.
        $approvedlist = new approved_contextlist($teacher, 'block_townsquare', [$this->testdata->studentcontext->id]);
        provider::delete_data_for_user($approvedlist);
        $this->assertEquals(2, count($DB->get_records('block_townsquare_preferences')));

        // Delete the teacher date in its own context.
        $approvedlist = new approved_contextlist($teacher, 'block_townsquare', [\context_user::instance($teacher->id)->id]);
        provider::delete_data_for_user($approvedlist);
        $record = $DB->get_record('block_townsquare_preferences', ['userid' => $this->testdata->student->id]);
        $this->assertEquals(1, count($DB->get_records('block_townsquare_preferences')));
        $this->assertEquals($this->testdata->student->id, $record->userid);
    }

    /**
     * Test deleting data within a contest for an approved userlist.
     * @return void
     */
    public function test_delete_data_for_users() {
        global $DB;
        // Add another user with a different context and add usersettings.
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $this->testdata->course->id, 'teacher');
        $this->helper_add_preference($this->testdata->student->id);
        $this->helper_add_preference($teacher->id);

        // Try a system context deletion, which should have no effect.
        provider::delete_data_for_all_users_in_context(\context_system::instance());
        $this->assertEquals(2, count($DB->get_records('block_townsquare_preferences')));

        // Try to delete user data in another users context, which should have no effect..
        $approvedlist = new approved_userlist($this->testdata->studentcontext, 'block_townsquare', [$teacher->id]);
        provider::delete_data_for_users($approvedlist);
        $this->assertEquals(2, count($DB->get_records('block_townsquare_preferences')));

        // Delete user data in the right context.
        $approvedlist = new approved_userlist($this->testdata->studentcontext, 'block_townsquare', [$this->testdata->student->id]);
        provider::delete_data_for_users($approvedlist);
        $this->assertEquals(1, count($DB->get_records('block_townsquare_preferences')));
        $record = $DB->get_record('block_townsquare_preferences', ['userid' => $teacher->id]);
        $this->assertEquals($teacher->id, $record->userid);

    }


    // Helper functions.

    /**
     * Helper function that sets up the test data.
     * @return void
     */
    private function helper_add_preference($userid) {
        global $DB;
        // Create a user preference for townsquare content filter.
        $this->testdata->setting = new \stdClass();
        $this->testdata->setting->userid = $userid;
        $this->testdata->setting->timefilterpast = 15778463;
        $this->testdata->setting->timefilterfuture = 15778463;
        $this->testdata->setting->basicletter = 1;
        $this->testdata->setting->completionletter = 1;
        $this->testdata->setting->postletter = 0;
        $DB->insert_record('block_townsquare_preferences', $this->testdata->setting);
    }
}
