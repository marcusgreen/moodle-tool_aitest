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
    'message' => '',
    'success' => false,
    'responsetext' => '',
    'prompttext' => $prompttext
];

if ($actionparam === 'test') {
    $templatedata['testsubmitted'] = true;
    $blockedhosts = $CFG->curlsecurityblockedhosts;
    $allowedports = $CFG->curlsecurityallowedport;

    $helper = new \core\files\curl_security_helper();

    // Pre-check: detect whether Moodle's own cURL security helper would block any
    // enabled AI provider endpoint (e.g. a non-allowed port or blocked host). A block
    // makes the underlying request fail with code 0, which core cannot map to an AI
    // error and instead throws "Invalid error code: 0". Catch that here with a clear
    // message before it happens.
    $blockedendpoints = [];
    if ($helper->is_enabled()) {
        $providers = $DB->get_records('ai_providers', ['enabled' => 1]);
        foreach ($providers as $provider) {
            $config = json_decode($provider->config);
            if (empty($config->endpoint)) {
                continue;
            }
            if ($helper->url_is_blocked($config->endpoint)) {
                $blockedendpoints[] = $provider->name . ' (' . $config->endpoint . ')';
            }
        }
    }

    if (str_starts_with($CFG->release, '5')) {
        $manager = new \core_ai\manager($DB);
    } else {
        $manager = new \core_ai\manager();
    }

    if ($blockedendpoints) {
        $result = null;
        $templatedata['message'] = get_string(
            'endpointblocked',
            'tool_aitest',
            (object) [
                'endpoints' => implode(', ', $blockedendpoints),
                'url' => (new moodle_url('/admin/settings.php', ['section' => 'httpsecurity']))->out(false),
            ]
        );
    } else {
        $result = $manager->process_action($action);
    }

    if ($result !== null) {
        ob_start();
        var_dump($result);
        $templatedata['result'] = ob_get_clean();

        if ($result->get_success()) {
            $templatedata['success'] = true;
            $templatedata['responsetext'] = $result->get_response_data()['generatedcontent'];
        } else {
            $templatedata['message'] = $result->get_errormessage();
        }
    }
}

echo $output->header();
echo $output->render_from_template('tool_aitest/coreai', $templatedata);
echo $output->footer();
