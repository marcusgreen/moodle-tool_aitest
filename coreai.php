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
require ('../../../config.php');

require_admin();

use curl;

$actionparam = optional_param('action', '', PARAM_ALPHA);
$url = new moodle_url('/admin/tool/aitest/coreai.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$context = context_system::instance();
$prompttext = 'Please respond to confirm I been successfull in connecting to you and return nothing else';
$action = new \core_ai\aiactions\generate_text(
    contextid: $context->id,
    userid: $USER->id,
    prompttext: $prompttext
);
global $DB, $CFG;
require_once($CFG->libdir . '/filelib.php');

$PAGE->set_heading($SITE->fullname);
$output = $PAGE->get_renderer('core');

$templatedata = [
    'wwwroot' => $CFG->wwwroot,
    'testsubmitted' => false,
    'result' => '',
    'message' => ''
];

if ($actionparam === 'test') {
    $templatedata['testsubmitted'] = true;
    $blockedhosts = $CFG->curlsecurityblockedhosts;
    $allowedports = $CFG->curlsecurityallowedport;

    $curl = new curl();
    $helper = new \core\files\curl_security_helper();
    if (str_starts_with($CFG->release, '5')) {
        $manager = new \core_ai\manager($DB);
    } else {
        $manager = new \core_ai\manager();
    }
    $result = $manager->process_action($action);

    ob_start();
    var_dump($result);
    $templatedata['result'] = ob_get_clean();

    if ($error = $result->get_errormessage()) {
        $templatedata['message'] = $error;
    } else {
        $templatedata['message'] = $result->get_response_data()['generatedcontent'];
    }
}

echo $output->header();
echo $output->render_from_template('tool_aitest/coreai', $templatedata);
echo $output->footer();
