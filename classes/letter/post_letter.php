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
 * Class that represents a post from the forum or moodleoverflow module.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_townsquare\letter;

use moodle_exception;
use moodle_url;
use stdClass;

/**
 * Class that represents a post from the forum or moodleoverflow module.
 *
 * Note: The forum module belongs to the core plugins of Moodle.
 *       Townsquare also supports the moodleoverflow plugin, if it is installed.
 * Subclass from letter.
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class post_letter extends letter {

    // Attributes.

    /** @var object information about the post author. The object contains:
     * - int id: the id of the author
     * - string name: full name of the author (first and last name combined)
     * - object picture: the profile picture of the author
     */
    private object $author;

    /** @var object attributes of the post. The object contains:
     * - int instanceid: The local ID of the module instance, is the forumid or moodleoverflowid
     * - int discussionid: The id of the discussion
     * - int id: the id of the post
     * - string message: the message of the post
     * - string discussionsubject: the subject of the discussion
     * - int postparentid: the id of the parent post
     * - bool anonymous: if the post is anonymous or not
     */
    private object $post;

    /** @var object additional urls that are used in the post letter. The object contains:
     * -  moodle_url linktopost: url to the discussion
     * -  moodle_url linktoauthor: url to the user that wrote the post
     */
    private object $posturls;

    /** @var bool variable for the mustache template */
    public bool $ispost = true;

    // Constructor.

    /**
     * Constructor of the post letter class.
     * Builds the content of the post letter, gathers additional information like links and a picture and gets the post ready
     * to export it to the mustache template.
     * @param int $contentid    internal ID in the townsquare block
     * @param object $postevent a post event with information,for more see classes/townsquareevents.php.
     * @throws moodle_exception
     */
    public function __construct($contentid, $postevent) {
        global $DB;
        parent::__construct($contentid, $postevent->courseid, $postevent->modulename, $postevent->instancename,
                            $postevent->postmessage, $postevent->postcreated, $postevent->coursemoduleid);

        $this->author = new stdClass();
        $this->post = new stdClass();
        $this->posturls = new stdClass();

        $this->lettertype = 'post';
        if ($postevent->modulename == 'forum') {
            $this->post->instanceid = $postevent->forumid;
            $this->post->anonymous = false;
        } else if ($postevent->modulename == 'moodleoverflow') {
            $this->post->instanceid = $postevent->moodleoverflowid;
            $this->post->anonymous = $postevent->anonymous;
        } else {
            throw new moodle_exception('invalidmodulename', 'block_townsquare');
        }
        $this->post->discussionid = $postevent->postdiscussion;
        $this->author->id = $postevent->postuserid;
        $author = $DB->get_record('user', ['id' => $postevent->postuserid]);
        $this->author->name = $author->firstname . ' ' . $author->lastname;
        $this->post->id = $postevent->postid;
        $this->post->message = $postevent->postmessage;
        $this->post->discussionsubject = $postevent->discussionsubject;
        $this->post->parentid = $postevent->postparentid;

        $this->build_links();
        $this->retrieve_profilepicture();
    }

    // Functions.

    /**
     * Export Function for the mustache template.
     * @return array
     */
    public function export_letter():array {
        return [
            'contentid' => $this->contentid,
            'lettertype' => $this->lettertype,
            'ispost' => $this->ispost,
            'courseid' => $this->courseid,
            'coursename' => $this->coursename,
            'modulename' => $this->modulename,
            'instancename' => $this->instancename,
            'discussionsubject' => $this->post->discussionsubject,
            'anonymous' => $this->post->anonymous,
            'authorname' => $this->author->name,
            'authorpicture' => $this->author->picture,
            'postid' => $this->post->id,
            'message' => format_text($this->post->message),
            'created' => date('d.m.Y', $this->created),
            'linktocourse' => $this->linktocourse->out(),
            'linktoactivity' => $this->linktoactivity->out(),
            'linktopost' => $this->posturls->linktopost->out(),
            'linktoauthor' => $this->posturls->linktoauthor->out(),
        ];
    }

    // Helper functions.

    /**
     * Function to build the links to the post, author.
     * @return void
     */
    private function build_links() {
        $this->posturls->linktoauthor = new moodle_url('/user/view.php', ['id' => $this->author->id]);
        if ($this->modulename == 'forum') {
            $this->posturls->linktopost = new moodle_url('/mod/forum/discuss.php',
                                                        ['d' => $this->post->discussionid], 'p' . $this->post->id);
        } else {
            $this->posturls->linktopost = new moodle_url('/mod/moodleoverflow/discussion.php',
                                                        ['d' => $this->post->discussionid], 'p' . $this->post->id);

            // If the post in the moodleoverflow is anonymous, the user should not be visible.
            if ($this->post->anonymous) {
                $this->posturls->linktoauthor = new moodle_url('');
                $this->author->id = -1;
                $this->author->name = 'anonymous';
            }
        }
    }

    /**
     * Method to retrieve the profile picture of the author.
     * @return void
     */
    private function retrieve_profilepicture() {
        global $DB, $OUTPUT;

        // Profile picture is only retrieved if the author is visible.
        if (!$this->post->anonymous) {
            $user = new stdClass();
            $picturefields = \core_user\fields::get_picture_fields();
            $user = username_load_fields_from_object($user, $DB->get_record('user', ['id' => $this->author->id]),
                                            null, $picturefields);
            $user->id = $this->author->id;
            $this->author->picture = $OUTPUT->user_picture($user, ['courseid' => $this->courseid, 'link' => false]);
        } else {
            $this->author->picture = '';
        }
    }

}
