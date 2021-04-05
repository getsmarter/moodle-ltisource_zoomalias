<?php
// This file is part of the Laerdal EcoHub LTI Source sub-plugin.
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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // General settings Header
    $name = 'ltisource_zoomalias/generaltitle';
    $heading = get_string('generaltitle', 'ltisource_zoomalias');
    $information = '';
    $setting = new admin_setting_heading($name, $heading, $information);
    $settings->add($setting);

    // Allowed LTI Path for triggering
    $name = 'ltisource_zoomalias/targetlti';
    $title = get_string('targetlti', 'ltisource_zoomalias');
    $description = get_string('targetltidesc', 'ltisource_zoomalias');
    $default = null;
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $settings->add($setting);
}
