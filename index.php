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
 * Make a call to the AI System to check if it is working
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

require_admin();

$url = new moodle_url('/admin/tool/aitest/index.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$context = context_system::instance();
$action = new \core_ai\aiactions\generate_text(
    contextid: $context->id,
    userid: $USER->id,
    prompttext: 'Please respond to confirm I been successfull in connecting to you and return nothing else'
);
$manager = new \core_ai\manager();
$azureactions = $manager->get_supported_actions('aiprovider_azureai');
$result = $manager->process_action($action);
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
var_dump($result);
$message = '';
 if ($error = $result->get_errormessage()) {
     $message = $error;
 } else {
     $message = $result->get_response_data()['generatedcontent'];
 }
echo '</br></br>';
$pluginmanager = core_plugin_manager::instance();
$aitestinfo = $pluginmanager->get_plugin_info('tool_aitest');
echo 'This is version '.$aitestinfo->versiondisk. ' of the tool_aitest plugin';
echo '<br/></br>';
echo $message;
echo $OUTPUT->footer();
