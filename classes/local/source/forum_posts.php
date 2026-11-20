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

namespace block_townsquare\local\source;

use block_townsquare\hook\extend_content;
use block_townsquare\item\post_item;
use context_module;
use core_user\fields;
use moodle_url;
use user_picture;

/**
 * Source for the posts of the forum module.
 *
 * It works like a callback of the hook extend_content and is called by \block_townsquare\local\service\items.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class forum_posts {
    /**
     * Adds the forum posts the user can see.
     * @param extend_content $hook
     */
    public static function extend_content(extend_content $hook): void {
        global $PAGE;

        foreach (self::get_forumposts_from_db($hook) as $post) {
            // Format the message.
            $context = context_module::instance($post->coursemoduleid);
            $message = file_rewrite_pluginfile_urls(
                $post->message,
                'pluginfile.php',
                $context->id,
                'mod_forum',
                'post',
                $post->id,
                ['includetoken' => true]
            );
            $message = format_text($message, $post->messageformat, ['para' => true, 'context' => $context]);

            // The author with its picture.
            $author = user_picture::unalias($post, null, 'authorid', 'author');
            $picture = new user_picture($author);
            $picture->size = 35;

            $hook->add_post(new post_item(
                courseid: $post->courseid,
                cmid: $post->coursemoduleid,
                discussionsubject: $post->discussionsubject,
                message: $message,
                time: $post->created,
                url: new moodle_url('/mod/forum/discuss.php', ['d' => $post->discussion], 'p' . $post->id),
                authorname: fullname($author),
                authorid: $author->id,
                authorurl: new moodle_url('/user/view.php', ['id' => $author->id]),
                pictureurl: $picture->get_url($PAGE),
                privatereplyto: $post->privatereplyto,
            ));
        }
    }

    /**
     * Searches for forum posts. The sql query makes sure that the forum module is enabled and the forum is visible.
     * Private replies are only found for their author and recipient.
     * @param extend_content $hook The user, the courses and the time span to search in
     * @return array
     */
    private static function get_forumposts_from_db(extend_content $hook): array {
        global $DB;
        // Prepare params for sql statement.
        if ($hook->courseids == []) {
            return [];
        }
        [$insqlcourses, $inparamscourses] = $DB->get_in_or_equal($hook->courseids, SQL_PARAMS_NAMED);
        $params = ['timestart' => $hook->timestart, 'userid' => $hook->userid, 'userid2' => $hook->userid] + $inparamscourses;
        $authorfields = fields::for_userpic()->get_sql('u', false, 'author', 'authorid')->selects;

        $sql = "SELECT posts.id,
                    cm.id AS coursemoduleid,
                    discuss.course AS courseid,
                    discuss.name AS discussionsubject,
                    posts.discussion,
                    posts.created,
                    posts.message,
                    posts.messageformat,
                    posts.privatereplyto
                    $authorfields
                FROM {forum_posts} posts
                JOIN {forum_discussions} discuss ON discuss.id = posts.discussion
                JOIN {forum} module ON module.id = discuss.forum
                JOIN {modules} modules ON modules.name = 'forum'
                JOIN {user} u ON u.id = posts.userid
                JOIN {course_modules} cm ON (cm.course = module.course AND cm.module = modules.id AND cm.instance = module.id)
                WHERE discuss.course $insqlcourses
                    AND posts.created > :timestart
                    AND cm.visible = 1
                    AND modules.visible = 1
                    AND ( posts.privatereplyto = 0 OR posts.userid = :userid OR posts.privatereplyto = :userid2)
                ORDER BY posts.created DESC;";

        // Get all posts.
        return $DB->get_records_sql($sql, $params);
    }
}
