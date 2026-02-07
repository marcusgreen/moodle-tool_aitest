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
 * Make a call to the Local AI Manager to check if it is working
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

require_admin();
$actionparam = optional_param('action', '', PARAM_ALPHA);
$url = new moodle_url('/admin/tool/aitest/localaimanager.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$context = context_system::instance();

$PAGE->set_heading($SITE->fullname);
$output = $PAGE->get_renderer('core');

$templatedata = [
    'testsubmitted' => false,
    'success' => false,
    'error' => false,
    'exception' => false,
    'response' => '',
    'debuginfo' => '',
    'exceptionmessage' => '',
    'exceptiontrace' => ''
];

if ($actionparam === 'test') {
    $templatedata['testsubmitted'] = true;
    
    // Test prompt
    $prompttext = 'Please respond to confirm I have been successful in connecting to you and return nothing else';
    
    try {
        // Create manager instance with 'generate_text' purpose
        $manager = new \local_ai_manager\manager('generate_text');
        
        // Make the request
        $response = $manager->perform_request(
            $prompttext,
            'tool_aitest',
            $context->id
        );
        
        // Display the result
        if ($response->get_code() === 200) {
            $templatedata['success'] = true;
            $templatedata['response'] = s($response->get_content());
        } else {
            $templatedata['error'] = true;
            $templatedata['response'] = s($response->get_errormessage());
            if ($response->get_debuginfo()) {
                $templatedata['debuginfo'] = s($response->get_debuginfo());
            }
        }
        
    } catch (\Exception $e) {
        $templatedata['exception'] = true;
        $templatedata['exceptionmessage'] = s($e->getMessage());
        $templatedata['exceptiontrace'] = s($e->getTraceAsString());
    }
}

echo $output->header();
echo $output->render_from_template('tool_aitest/localaimanager', $templatedata);
echo $output->footer();
