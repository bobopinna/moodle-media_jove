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
 * Test classes for handling embedded media.
 *
 * @package media_jove
 * @copyright 2020 Roberto Pinna
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace media_jove;

use core_media_manager;

/**
 * Test script for media embedding.
 *
 * @package media_jove
 * @copyright 2020 Roberto Pinna
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class player_test extends \advanced_testcase {

    /**
     * Pre-test setup. Preserves $CFG.
     */
    public function setUp() {
        parent::setUp();

        // Reset $CFG and $SERVER.
        $this->resetAfterTest();

        // Consistent initial setup: all players disabled.
        \core\plugininfo\media::set_enabled_plugins('jove');

        // Pretend to be using Firefox browser (must support ogg for tests to work).
        \core_useragent::instance(true, 'Mozilla/5.0 (X11; Linux x86_64; rv:46.0) Gecko/20100101 Firefox/46.0 ');
    }

    /**
     * Test that plugin is returned as enabled media plugin.
     *
     * @covers \media_jove_plugin
     */
    public function test_is_installed() {
        $sortorder = \core\plugininfo\media::get_enabled_plugins();
        $this->assertEquals(['jove' => 'jove'], $sortorder);
    }

    /**
     * Test supported link types
     *
     * @covers \media_jove_plugin
     * @covers ::embed_external
     */
    public function test_supported() {
        $manager = core_media_manager::instance();

        // Format: jove.
        $url = new \moodle_url('https://www.jove.com/video/60144/a-blood-free-diet-to-rear-anopheline-mosquitoes');
        $t = $manager->embed_url($url);
        $this->assertContains('</iframe>', $t);
        $url = new \moodle_url('https://www.jove.com/science-education/10552/scientific-method');
        $t = $manager->embed_url($url);
        $this->assertContains('</iframe>', $t);

        // Format: jove video with invalid parameter values (injection attempts).
        $inject = '?index=4&list=PLxcO_">';
        $url = new \moodle_url('https://www.jove.com/video/60144/a-blood-free-diet-to-rear-anopheline-mosquitoes'.$inject);
        $t = $manager->embed_url($url);
        $this->assertContains('</iframe>', $t);
        $this->assertNotContains('list=PLxcO_', $t); // We shouldn't get a list param as input was invalid.
    }

    /**
     * Test embedding without media filter (for example for displaying URL resorce).
     *
     * @covers \media_jove_plugin
     * @covers ::embed_external
     */
    public function test_embed_url() {
        global $CFG;

        $url = new \moodle_url('https://www.jove.com/video/60144/a-blood-free-diet-to-rear-anopheline-mosquitoes?pause');

        $manager = core_media_manager::instance();
        $embedoptions = array(
            core_media_manager::OPTION_TRUSTED => true,
            core_media_manager::OPTION_BLOCK => true,
        );

        $this->assertTrue($manager->can_embed_url($url, $embedoptions));
        $content = $manager->embed_url($url, 'Test & file', 0, 0, $embedoptions);

        $this->assertRegExp('~mediaplugin_jove~', $content);
        $this->assertRegExp('~</iframe>~', $content);
        $this->assertRegExp('~width="460" height="365"~', $content);

        // Repeat sending the specific size to the manager.
        $content = $manager->embed_url($url, 'New file', 123, 50, $embedoptions);
        $this->assertRegExp('~width="123" height="50"~', $content);
    }

    /**
     * Test that mediaplugin filter replaces a link to the supported file with media tag.
     *
     * filter_mediaplugin is enabled by default.
     *
     * @covers \media_jove_plugin
     * @covers ::embed_external
     */
    public function test_embed_link() {
        global $CFG;
        $url = new \moodle_url('https://www.jove.com/video/60144/a-blood-free-diet-to-rear-anopheline-mosquitoes?pause');
        $text = \html_writer::link($url, 'Watch this one');
        $content = format_text($text, FORMAT_HTML);

        $this->assertRegExp('~mediaplugin_jove~', $content);
        $this->assertRegExp('~</iframe>~', $content);
        $this->assertRegExp('~width="460" height="365"~', $content);
    }

    /**
     * Test that mediaplugin filter adds player code on top of <video> tags.
     *
     * filter_mediaplugin is enabled by default.
     *
     * @covers \media_jove_plugin
     * @covers ::embed_external
     */
    public function test_embed_media() {
        global $CFG;
        $url = new \moodle_url('https://www.jove.com/video/60144/a-blood-free-diet-to-rear-anopheline-mosquitoes?pause');
        $trackurl = new \moodle_url('http://example.org/some_filename.vtt');
        $text = '<video controls="true"><source src="'.$url.'"/>' .
            '<track src="'.$trackurl.'">Unsupported text</video>';
        $content = format_text($text, FORMAT_HTML);

        $this->assertRegExp('~mediaplugin_jove~', $content);
        $this->assertRegExp('~</iframe>~', $content);
        $this->assertRegExp('~width="460" height="365"~', $content);
        // Video tag, unsupported text and tracks are removed.
        $this->assertNotRegExp('~</video>~', $content);
        $this->assertNotRegExp('~<source\b~', $content);
        $this->assertNotRegExp('~Unsupported text~', $content);
        $this->assertNotRegExp('~<track\b~i', $content);

        // Video with dimensions and source specified as src attribute without <source> tag.
        $text = '<video controls="true" width="123" height="35" src="'.$url.'">Unsupported text</video>';
        $content = format_text($text, FORMAT_HTML);
        $this->assertRegExp('~mediaplugin_jove~', $content);
        $this->assertRegExp('~</iframe>~', $content);
        $this->assertRegExp('~width="123" height="35"~', $content);
    }
}
