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
 * TODO describe file settings
 *
 * @package    tool_aitest
 * @copyright  2025 2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


if ($hassiteconfig) {
    $settings = new admin_settingpage('tool_aitest', get_string('pluginname', 'tool_aitest'));

    $url = new \moodle_url('../admin/tool/aitest/index.php');
    $link = \html_writer::link($url, get_string('testaiservices', 'tool_aitest'));
    $settings->add(new admin_setting_heading('testaiconfiguration', '',
    new \lang_string('testaiconfiguration', 'tool_aitest', $link)));
    $ADMIN->add('tools', $settings);

}