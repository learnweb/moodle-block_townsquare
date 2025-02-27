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
use context_module;
use core_user\fields;
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
     */
    public function __construct($contentid, $postevent) {
        parent::__construct($contentid, $postevent->courseid, $postevent->modulename, $postevent->instancename,
                            $postevent->content, $postevent->timestart, $postevent->coursemoduleid);

        $this->author = new stdClass();
        $this->post = new stdClass();
        $this->posturls = new stdClass();

        $this->lettertype = 'post';
        $this->lettercolor = townsquare_get_colorsetting('postletter');
        $this->post->instanceid = $postevent->instanceid;
        $this->post->discussionid = $postevent->postdiscussion;
        $this->post->id = $postevent->postid;
        $this->post->message = $this->format_post($postevent);
        $this->post->discussionsubject = $postevent->discussionsubject;
        $this->post->parentid = $postevent->postparentid;
        $this->post->anonymous = $postevent->anonymous ?? false;
        $this->author->id = $postevent->postuserid;
        $this->author->name = $postevent->postuserfirstname . ' ' . $postevent->postuserlastname;
        $this->posturls->linktopost = $postevent->linktopost;
        $this->posturls->linktoauthor = $postevent->linktoauthor;

        $this->add_anonymousattribute($postevent);
        $this->add_privatereplyattribute($postevent);
        $this->retrieve_profilepicture();
    }

    // Functions.

    /**
     * Export Function for the mustache template.
     * @return array
     */
    public function export_letter(): array {
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
            'privatereplyfrom' => $this->post->privatereplyfrom,
            'privatereplyto' => $this->post->privatereplyto,
            'authorname' => $this->author->name,
            'authorpicture' => $this->author->picture,
            'postid' => $this->post->id,
            'message' => $this->post->message,
            'created' => date('d.m.Y', $this->created),
            'createdtimestamp' => $this->created,
            'linktocourse' => $this->linktocourse->out(),
            'linktoactivity' => $this->linktoactivity->out(),
            'linktopost' => $this->posturls->linktopost->out(),
            'linktoauthor' => $this->posturls->linktoauthor->out(),
            'postlettercolor' => $this->lettercolor,
        ];
    }

    // Helper functions.

    /**
     * Method to retrieve the profile picture of the author.
     * @return void
     */
    private function retrieve_profilepicture() {
        global $DB, $OUTPUT;

        // Profile picture is only retrieved if the author is visible.
        if ($this->post->anonymous) {
            $this->author->picture = '';
            return;
        }
        $user = new stdClass();
        $picturefields = fields::get_picture_fields();
        $user = username_load_fields_from_object($user, $DB->get_record('user', ['id' => $this->author->id]),
            null, $picturefields);
        $user->id = $this->author->id;
        $this->author->picture = $OUTPUT->user_picture($user, ['courseid' => $this->courseid, 'link' => false]);
    }

    /**
     * Method to add the anonymous attribute to the post.
     * @param object $postevent a post event with information,for more see classes/townsquareevents.php.
     * @return void
     */
    private function add_anonymousattribute($postevent): void {
        if ($postevent->modulename != 'moodleoverflow') {
            $this->post->anonymous = false;
            return;
        }
        $this->post->anonymous = $postevent->anonymous;
    }

    /**
     * Method to add a boolean that indicates if the post is a private reply.
     * @param $postevent
     * @return void
     */
    private function add_privatereplyattribute($postevent): void {
        if ($postevent->modulename == 'forum') {
            // Check if the private author is the same private recipient.
            if ($postevent->privatereplyto && $postevent->privatereplyfrom) {
                $this->post->privatereplyto = false;
                $this->post->privatereplyfrom = true;
            } else {
                $this->post->privatereplyto = $postevent->privatereplyto;
                $this->post->privatereplyfrom = $postevent->privatereplyfrom;
            }
        } else {
            $this->post->privatereplyto = false;
            $this->post->privatereplyfrom = false;
        }
    }

    /**
     * Function to format the post message before exporting it to the mustache template.
     * @param $postevent
     * @return string
     */
    private function format_post($postevent) {
        $context = context_module::instance($postevent->coursemoduleid);
        $message = file_rewrite_pluginfile_urls($postevent->content, 'pluginfile.php', $context->id,
                                    'mod_'. $postevent->modulename, 'post', $postevent->postid, ['includetoken' => true]);
        $options = new stdClass();
        $options->para = true;
        $options->context = $context;
        return format_text($message, $postevent->postmessageformat, $options);
    }

}
