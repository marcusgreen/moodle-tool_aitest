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
 * DiagnosticsCore AI setup
 *
 * @package  tool_aitest
 * @copyright 2025 Marcus Green
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_admin();
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/lib/outputlib.php');
require_once($CFG->libdir . '/weblib.php'); // For format_text
require_once(__DIR__ . '/lib.php');

// The AI manager class needs to be properly loaded
require_once($CFG->dirroot . '/ai/classes/manager.php');
require_once($CFG->libdir . '/classes/plugininfo/base.php');
require_once($CFG->libdir . '/classes/plugin_manager.php');

// Get the AI manager.
$aimanager = new \core_ai\manager($DB);

// Collect diagnostic information.
$diagnostics = "# " . get_string('diagnosticsreport', 'tool_aitest') . "\n\n";
$diagnostics .= "## " . get_string('moodleversion', 'tool_aitest') . "\n";
$diagnostics .= get_string('moodleversioninfo', 'tool_aitest', ['version' => $CFG->version, 'release' => $CFG->release]) . "\n\n";

// Get active providers.
$diagnostics .= tool_aitest_get_active_providers_diagnostics($aimanager);

// Get active placements.
$diagnostics .= tool_aitest_get_active_placements_diagnostics();

// Set headers for file download.
$filename = 'tool_aitest_diagnostics_' . date('Y-m-d_H-i-s') . '.md';

if (optional_param('download', 0, PARAM_BOOL)) {
    // Set headers for file download and output diagnostics.
    header('Content-Type: text/markdown');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($diagnostics));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $diagnostics;
    exit;
} else {
    // Display the content in the browser with proper headers.
    $PAGE->set_context(context_system::instance());
    $PAGE->set_url(new moodle_url('/tool_aitest/diagnose.php'));
    $PAGE->set_title(get_string('diagnosticsreport', 'tool_aitest'));
    $output = $PAGE->get_renderer('core'); // Use core renderer

    // Convert markdown to HTML.
    $diagnosticshtml = format_text($diagnostics, FORMAT_MARKDOWN);

    $templatedata = [
        'diagnostics' => $diagnosticshtml,
    ];

    echo $output->header();
    echo '<a href="index.php" class="btn btn-secondary mb-3">Back to Index</a><br/><br/>';
    echo $output->render_from_template('tool_aitest/diagnose', $templatedata);
    echo $output->footer();
    exit;
}
