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
 * Main class for plugin 'media_jove'
 *
 * @package   media_jove
 * @copyright 2020 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Player that creates jove embedding.
 *
 * @package   media_jove
 * @copyright 2020 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class media_jove_plugin extends core_media_player_external {
    /**
     * Stores whether the playlist regex was matched last time when
     * {@link list_supported_urls()} was called
     * @var bool
     */

    public function list_supported_urls(array $urls, array $options = array()) {
        // These only work with a SINGLE url (there is no fallback).
        if (count($urls) == 1) {
            $url = reset($urls);

            // Check against regex.
            if (preg_match($this->get_regex(), $url->out(false), $this->matches)) {
                return array($url);
            }
        }

        return array();
    }

    protected function embed_external(moodle_url $url, $name, $width, $height, $options) {

        $info = trim($name);
        if (empty($info) or strpos($info, 'http') === 0) {
            $info = get_string('pluginname', 'media_jove');
        }
        $info = s($info);

        if (empty($width)) {
            $width = 460;
            $height = 415;
        }
        self::pick_video_size($width, $height);

        $videoid = end($this->matches);

        return <<<OET
<span class="mediaplugin mediaplugin_jove">
<iframe title="$info" width="$width" height="$height"
  src="https://www.jove.com/embed/player?id=$videoid&t=1&s=1&fpv=1" frameborder="0" allowfullscreen="1"><p><a title="$info" href="$url">$info</a></p></iframe>
</span>
OET;

    }

    /**
     * Returns regular expression used to match URLs for single jove video
     * @return string PHP regular expression e.g. '~^https?://example.org/~'
     */
    protected function get_regex() {
        // Regex for standard jove link.
        $link = '(jove\.com/(video|science-education|embed/directions)/)';

        // Initial part of link.
        $start = '~^https?://((www)\.)?(' . $link . ')';
        // Middle bit: Video key value.
        $middle = '([0-9]+)';
        return $start . $middle . core_media_player_external::END_LINK_REGEX_PART;
    }

    public function get_embeddable_markers() {
        return array('jove.com');
    }

    /**
     * Default rank
     * @return int
     */
    public function get_rank() {
        return 101;
    }
}