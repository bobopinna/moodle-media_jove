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
 * Settings file for plugin 'media_jove'
 *
 * @package   media_jove
 * @copyright 2020 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $features = array();
    $features['title'] = new lang_string('title', 'media_jove');
    $features['author'] = new lang_string('author', 'media_jove');
    $features['info'] = new lang_string('articledescription', 'media_jove');
    $features['chapters'] = new lang_string('chapters', 'media_jove');
    $features['pause'] = new lang_string('pausevideo', 'media_jove');

    $defaultfeatures = array('title' => 1, 'pause' => 1);
    $settings->add(new admin_setting_configmulticheckbox('media_jove/features',
        new lang_string('features', 'media_jove'),
        new lang_string('configfeatures', 'media_jove'), $defaultfeatures, $features));

}
