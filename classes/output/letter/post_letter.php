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

namespace block_townsquare\output\letter;

use block_townsquare\item\post_item;
use core\output\renderer_base;

/**
 * A letter for posts. It shows the post as the module of the post provides it.
 *
 * @package     block_townsquare
 * @copyright   2023 Tamaro Walter
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class post_letter extends letter {
    /** @var post_item The post of the letter */
    private post_item $post;

    /**
     * Constructor of the post letter.
     *
     * @param int $contentid internal ID in the townsquare block
     * @param post_item $post
     */
    public function __construct(int $contentid, post_item $post) {
        parent::__construct($contentid, $post->courseid, $post->cmid, $post->time);
        $this->post = $post;
    }

    #[\Override]
    public function get_template_name(renderer_base $renderer): string {
        return 'block_townsquare/letter/post_letter';
    }

    #[\Override]
    public function export_for_template(renderer_base $output): array {
        global $USER;

        // A private reply that the user wrote is always shown as "from", even when it was sent to themselves.
        $isprivatereply = $this->post->privatereplyto !== 0;
        $privatereplyfrom = $isprivatereply && $this->post->authorid === (int) $USER->id;
        $privatereplyto = $isprivatereply && !$privatereplyfrom && $this->post->privatereplyto === (int) $USER->id;

        return $this->export_basics($output) + [
            'discussionsubject' => $this->post->discussionsubject,
            'message' => $this->post->message,
            'authorname' => $this->post->authorname,
            'authorpictureurl' => ($this->post->pictureurl ?? $output->image_url('u/f2'))->out(false),
            'linktoauthor' => $this->post->authorurl?->out(),
            'linktopost' => $this->post->url->out(),
            'privatereplyfrom' => $privatereplyfrom,
            'privatereplyto' => $privatereplyto,
        ];
    }
}
