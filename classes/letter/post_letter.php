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

namespace block_townsquare\letter;

defined('MOODLE_INTERNAL') || die();

/**
 * Class that represents a post from the forum or moodleoverflow.
 * Subclass from letter.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class post_letter extends letter {

    // Attributes.

    // From parent class: lettertype, course, modulename, created.

    /** @var int The course module id */
    private $coursemoduleid;

    /** @var int The local ID of the module  instance, is the forumid or moodleoverflowid*/
    private $localmoduleid;

    /** @var int The id of the discussion */
    private $discussionid;

    /** @var int the id of the author of the post */
    private $author;

    /** @var int the id of the post */
    private $postid;

    /** @var string the message of the post */
    private $message;

    /** @var string the subject of the discussion */
    private $subject;

    /** @var int the id of the parent post */
    private $postparentid;

    // Urls Attributes.

    /** @var \moodle_url url to the course */
    private $linktocourse;

    /** @var \moodle_url url to the module instance*/
    private $linktomoduleinstance;

    /** @var \moodle_url url to the discussion */
    private $linktopost;

    /** @var \moodle_url url to the user that wrote the post */
    private $linktoauthor;

    // Constructor.

    /**
     * @param $postevent object a post event with information,for more see classes/townsquareevents.php.
     * @throws \moodle_exception
     */
    public function __construct($postevent) {
        parent::__construct($postevent->courseid, $postevent->modulename, $postevent->message, $postevent->postcreated);
        $this->lettertype = 'post';
        if ($postevent->modulename == 'forum') {
            $this->localmoduleid = $postevent->forumid;
        } else if ($postevent->modulename == 'moodleoverflow') {
            $this->localmoduleid = $postevent->moodleoverflowid;
        } else {
            throw new \moodle_exception('invalidmodulename', 'block_townsquare');
        }
        $this->coursemoduleid = get_coursemodule_from_instance($postevent->modulename, $this->localmoduleid);
        $this->discussionid = $postevent->discussionid;
        $this->author = $postevent->author;
        $this->postid = $postevent->postid;
        $this->message = $postevent->message;
        $this->subject = $postevent->subject;
        $this->postparentid = $postevent->postparentid;

        // If the post is an answer post, add an 'RE' to the subject.
        if ($this->postparentid == 0) {
            $this->subject = 'RE: ' . $this->subject;
        }

        $this->build_links();
    }

    // Getter for every attribute.

    /**
     * Overrides function from superclass.
     * @param $
     * @return string
     */
    public function get_lettertype() {
        return $this->lettertype;
    }

    /**
     * @return int
     */
    public function get_localmoduleid() {
        return $this->localmoduleid;
    }

    /**
     * @return int
     */
    public function get_discussionid() {
        return $this->discussionid;
    }

    /**
     * @return int
     */
    public function get_author() {
        return $this->author;
    }

    /**
     * @return int
     */
    public function get_postid() {
        return $this->postid;
    }

    /**
     * @return string
     */
    public function get_message() {
        return $this->message;
    }

    /**
     * @return string
     */
    public function get_subject() {
        return $this->subject;
    }

    /**
     * @return int
     */
    public function get_postparentid() {
        return $this->postparentid;
    }

    /**
     * @return \moodle_url
     */
    public function get_linktomoduleinstance() {
        return $this->linktomoduleinstance;
    }

    /**
     * @return \moodle_url
     */
    public function get_linktopost() {
        return $this->linktopost;
    }

    /**
     * @return \moodle_url
     */
    public function get_linktoauthor() {
        return $this->linktoauthor;
    }

    // Helper functions.

    /**
     * Function to build the links to the post, author.
     * @return void
     */
    private function build_links() {
        $this->linktocourse = new \moodle_url('/course/view.php', array('id' => $this->course));
        $this->linktoauthor = new \moodle_url('/user/view.php', array('id' => $this->author));

        if ($this->modulename == 'forum') {
            $this->linktomoduleinstance = new \moodle_url('mod/forum/view.php', array('id' => $this->coursemoduleid));
            $this->linktopost = new \moodle_url('/mod/forum/discuss.php',
                array('d' => $this->discussionid), 'p' . $this->postid);
        } else {
            $this->linktomoduleinstance = new \moodle_url('mod/moodleoverflow/view.php', array('id' => $this->coursemoduleid));
            $this->linktopost = new \moodle_url('/mod/moodleoverflow/discussion.php',
                array('d' => $this->discussionid), 'p' . $this->postid);

            // If the post in the moodleoverflow is anonymous, the user should not be visible.
            if ($this->anonymous) {
                $this->linktoauthor = '';
                $this->author = false;
            }
        }
    }

}
