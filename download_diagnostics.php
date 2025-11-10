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
 * Download diagnostics for qtype_aitext
 *
 * @package    qtype_aitext
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
xdebug_break();
require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/lib/outputlib.php');
require_once($CFG->libdir . '/weblib.php'); // For format_text

// The AI manager class needs to be properly loaded
require_once($CFG->dirroot . '/ai/classes/manager.php');
require_once($CFG->libdir . '/classes/plugininfo/base.php');
require_once($CFG->libdir . '/classes/plugin_manager.php');

// Get the AI manager.
$aimanager = new \core_ai\manager($DB);

// Collect diagnostic information.
$diagnostics = "# qtype_aitext Diagnostics Report\n\n";
$diagnostics .= "## Moodle Version\n";
$diagnostics .= "Moodle version: {$CFG->version} ({$CFG->release})\n\n";

 // Get active providers.
  $diagnostics .= "## Active AI Providers\n";
  $providers = $aimanager->get_provider_instances();
  if (empty($providers)) {
      $diagnostics .= "No active AI providers configured.\n\n";
  } else {
      foreach ($providers as $provider) {
          $status = $provider->enabled ? "enabled" : "disabled";
          $diagnostics .= "- {$provider->name} ({$provider->provider}) - {$status}\n";
      }
      $diagnostics .= "\n";
  }

 // Get active placements.
  $diagnostics .= "## Active AI Placements\n";
  // Use the plugin manager to get all aiplacement plugins
  $pluginmanager = core\plugin_manager::instance();
  $placements = $pluginmanager->get_plugins_of_type('aiplacement');
  $activeplacements = [];
  foreach ($placements as $component => $plugin) {
      if ($plugin->is_installed_and_upgraded()) {
          $activeplacements[$component] = $plugin;
      }
  }
  if (empty($activeplacements)) {
      $diagnostics .= "No active AI placements configured.\n\n";
  } else {
      foreach ($activeplacements as $component => $placement) {
          $status = $placement->is_enabled() ? "enabled" : "disabled";
          $diagnostics .= "- {$component} - {$status}\n";
      }
      $diagnostics .= "\n";
  }

// Set headers for file download.
$filename = 'qtype_aitext_diagnostics_' . date('Y-m-d_H-i-s') . '.md';

if (isset($_GET['download']) && $_GET['download'] == 1) {
    // Set headers for file download.
    header('Content-Type: text/markdown');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($diagnostics));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $diagnostics;
    exit;
} else {
    // Display the content in the browser.
    $PAGE->set_context(context_system::instance());
    $PAGE->set_url(new moodle_url('/question/type/aitext/download_diagnostics.php'));
    $PAGE->set_title(get_string('diagnosticsreport', 'qtype_aitext'));
    $output = $PAGE->get_renderer('core'); // Use core renderer

    // Convert markdown to HTML.
    $diagnosticshtml = format_text($diagnostics, FORMAT_MARKDOWN);

    $template_data = [
        'diagnostics' => $diagnosticshtml,
    ];

    echo $output->header();
    echo $output->render_from_template('qtype_aitext/ci_all_diagnostics', $template_data);
    echo $output->footer();
    exit;
}

