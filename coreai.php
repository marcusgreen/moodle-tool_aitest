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
$provider = optional_param('provider', '', PARAM_PLUGIN);
$providerid = optional_param('providerid', 0, PARAM_INT);
// Set when the test was launched from the core AI provider action settings form, so
// the admin can get back to the settings they just saved.
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

$urlparams = ['action' => $actionparam];
if ($provider !== '') {
    $urlparams['provider'] = $provider;
}
if ($providerid) {
    $urlparams['providerid'] = $providerid;
}
if ($returnurl !== '') {
    $urlparams['returnurl'] = $returnurl;
}
$url = new moodle_url('/admin/tool/aitest/coreai.php', $urlparams);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$output = $PAGE->get_renderer('core');

$templatedata = [
    'wwwroot' => $CFG->wwwroot,
    'testsubmitted' => false,
    'result' => '',
    'message' => '',
    'success' => false,
    'responsetext' => '',
    'prompttext' => \tool_aitest\tester::PROMPT,
    'returnurl' => $returnurl !== '' ? (new moodle_url($returnurl))->out(false) : '',
    'testurl' => (new moodle_url('/admin/tool/aitest/coreai.php',
        array_merge($urlparams, ['action' => 'test'])))->out(false),
];

if ($actionparam === 'test') {
    $templatedata = array_merge($templatedata, \tool_aitest\tester::run());
    $templatedata['testsubmitted'] = true;
}

echo $output->header();
echo $output->render_from_template('tool_aitest/coreai', $templatedata);
echo $output->footer();
