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
 * Class to show content to the user
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_townsquare\letter;

use moodle_exception;
use moodle_url;

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

    /** @var int The course module id */
    private $coursemoduleid;

    /** @var int The local ID of the module instance, is the forumid or moodleoverflowid */
    private $localmoduleid;

    /** @var string The name of the module instance */
    private $localname;

    /** @var int The id of the discussion */
    private $discussionid;

    /** @var int the id of the author of the post */
    private $author;

    /** @var object the profile picture of the author */
    private $authorpicture;

    /** @var string the name of the author */
    private $authorname;

    /** @var int the id of the post */
    private $postid;

    /** @var string the message of the post */
    private $message;

    /** @var string the subject of the discussion */
    private $discussionsubject;

    /** @var int the id of the parent post */
    private $postparentid;

    /** @var bool variable for the mustache template */
    public $ispost = true;

    /** @var bool if the post is anonymous or not */
    private $anonymous;

    // Urls Attributes.

    /** @var moodle_url url to the module instance */
    private $linktomoduleinstance;

    /** @var moodle_url url to the discussion */
    private $linktopost;

    /** @var moodle_url url to the user that wrote the post */
    private $linktoauthor;

    // Constructor.

    /**
     * Constructor of the post letter class.
     * @param int $contentid    internal ID in the townsquare block
     * @param object $postevent a post event with information,for more see classes/townsquareevents.php.
     * @throws moodle_exception
     */
    public function __construct($contentid, $postevent) {
        global $DB;
        parent::__construct($contentid, $postevent->courseid, $postevent->modulename,
                            $postevent->postmessage, $postevent->postcreated);
        $this->lettertype = 'post';
        if ($postevent->modulename == 'forum') {
            $this->localmoduleid = $postevent->forumid;
        } else if ($postevent->modulename == 'moodleoverflow') {
            $this->localmoduleid = $postevent->moodleoverflowid;
            $this->anonymous = $postevent->anonymous;
        } else {
            throw new moodle_exception('invalidmodulename', 'block_townsquare');
        }
        $this->coursemoduleid = get_coursemodule_from_instance($postevent->modulename, $this->localmoduleid)->id;
        $this->discussionid = $postevent->postdiscussion;
        $this->localname = $postevent->localname;
        $this->author = $postevent->postuserid;
        $author = $DB->get_record('user', ['id' => $postevent->postuserid]);
        $this->authorname = $author->firstname . ' ' . $author->lastname;
        $this->postid = $postevent->postid;
        $this->message = $postevent->postmessage;
        $this->discussionsubject = $postevent->discussionsubject;
        $this->postparentid = $postevent->postparent;

        $this->build_links();
        $this->retrieve_profilepicture();
    }

    // Functions.

    /**
     * Export Function for the mustache template.
     * @return array
     */
    public function export_letter():array {

        // Change the created timestamp to a date.
        $date = date('d.m.Y', $this->created);

        // Prepare the message.
        $message = format_text($this->message);

        return [
            'contentid' => $this->contentid,
            'lettertype' => $this->lettertype,
            'ispost' => $this->ispost,
            'coursemoduleid' => $this->coursemoduleid,
            'courseid' => $this->courseid,
            'coursename' => $this->coursename,
            'modulename' => $this->modulename,
            'localmoduleid' => $this->localmoduleid,
            'localname' => $this->localname,
            'discussionid' => $this->discussionid,
            'discussionsubject' => $this->discussionsubject,
            'author' => $this->author,
            'authorname' => $this->authorname,
            'authorpicture' => $this->authorpicture,
            'postid' => $this->postid,
            'message' => $message,
            'postparentid' => $this->postparentid,
            'created' => $date,
            'linktocourse' => $this->linktocourse->out(),
            'linktomoduleinstance' => $this->linktomoduleinstance->out(),
            'linktopost' => $this->linktopost->out(),
            'linktoauthor' => $this->linktoauthor->out(),
        ];
    }

    // Getter for every attribute.

    /**
     * Overrides function from superclass.
     * @return string
     */
    public function get_lettertype():string {
        return $this->lettertype;
    }

    /**
     * Getter for the local module id.
     * @return int
     */
    public function get_localmoduleid():int {
        return $this->localmoduleid;
    }

    /**
     * Getter for the discussion id.
     * @return int
     */
    public function get_discussionid():int {
        return $this->discussionid;
    }

    /**
     * Getter for the author id.
     * @return int
     */
    public function get_author():int {
        return $this->author;
    }

    /**
     * Getter for the post id.
     * @return int
     */
    public function get_postid():int {
        return $this->postid;
    }

    /**
     * Getter for the post message.
     * @return string
     */
    public function get_message():string {
        return $this->message;
    }

    /**
     * Getter for the name of the discussion.
     * @return string
     */
    public function get_discussionsubject():string {
        return $this->discussionsubject;
    }

    /**
     * Getter for the id of the parent post.
     * @return int
     */
    public function get_postparentid():int {
        return $this->postparentid;
    }

    /**
     * Getter for the instance id in the local database of the plugin.
     * @return moodle_url
     */
    public function get_linktomoduleinstance():moodle_url {
        return $this->linktomoduleinstance;
    }

    /**
     * Getter for the link to the post.
     * @return moodle_url
     */
    public function get_linktopost():moodle_url {
        return $this->linktopost;
    }

    /**
     * Getter for the link to the author.
     * @return moodle_url
     */
    public function get_linktoauthor():moodle_url {
        return $this->linktoauthor;
    }

    // Helper functions.

    /**
     * Function to build the links to the post, author.
     * @return void
     */
    private function build_links() {
        $this->linktoauthor = new moodle_url('/user/view.php', ['id' => $this->author]);

        if ($this->modulename == 'forum') {
            $this->linktomoduleinstance = new moodle_url('/mod/forum/view.php', ['id' => $this->coursemoduleid]);
            $this->linktopost = new moodle_url('/mod/forum/discuss.php', ['d' => $this->discussionid], 'p' . $this->postid);
        } else {
            $this->linktomoduleinstance = new moodle_url('/mod/moodleoverflow/view.php', ['id' => $this->coursemoduleid]);
            $this->linktopost = new moodle_url('/mod/moodleoverflow/discussion.php',
                ['d' => $this->discussionid], 'p' . $this->postid);

            // If the post in the moodleoverflow is anonymous, the user should not be visible.
            if ($this->anonymous) {
                $this->linktoauthor = new moodle_url('');
                $this->author = false;
                $this->authorname = 'anonymous';
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
        if ($this->author) {
            $user = new \stdClass();
            $picturefields = \core_user\fields::get_picture_fields();
            $user = username_load_fields_from_object($user, $DB->get_record('user', ['id' => $this->author]),
                                            null, $picturefields);
            $user->id = $this->author;
            $this->authorpicture = $OUTPUT->user_picture($user, ['courseid' => $this->courseid, 'link' => false]);
        }

    }

}
