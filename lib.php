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
 * AI Test tool functions.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extends the navigation with the report items
 *
 * This callback is called when the navigation for the tool_aitest plugin is being built.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course object
 * @param stdClass $context The context object
 */
function tool_aitest_extend_navigation(navigation_node $navigation, stdClass $course, stdClass $context) {
    // This function can be used to extend navigation if needed
}

/**
 * Extends the settings navigation with the report items
 *
 * This callback is called when the settings navigation for the tool_aitest plugin is being built.
 *
 * @param settings_navigation $settingsnav The settings navigation object
 * @param stdClass $context The context object
 */
function tool_aitest_extend_settings_navigation(settings_navigation $settingsnav, stdClass $context) {
    global $CFG;

    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $settingsnav->add(new admin_externalpage(
            'tool_aitest_aiactiongeneratetext',
            get_string('report:aiactiongeneratetext', 'tool_aitest'),
            new moodle_url('/admin/tool/aitest/report/ai_action_generate_text.php'),
            'moodle/site:config'
        ));
    }
}

/**
 * Callback function that returns the list of report builder datasources provided by this plugin
 *
 * @return array
 */
function tool_aitest_reportbuilder_data_sources(): array {
    return [
        'ai_action_generate_text' => \tool_aitest\reportbuilder\datasource\ai_action_generate_text::class
    ];
}

/**
 * Get the active providers diagnostics.
 *
 * @param \core_ai\manager $aimanager The AI manager.
 * @return string The diagnostics string.
 */
function tool_aitest_get_active_providers_diagnostics(\core_ai\manager $aimanager): string {
    $url = new moodle_url('/admin/settings.php', ['section' => 'aiprovider']);
    $diagnostics = "## [" . get_string('activeproviders', 'tool_aitest') . "](" . $url->out(false) . ")\n";
    $providers = $aimanager->get_provider_instances();
    if (empty($providers)) {
        $diagnostics .= get_string('noactiveproviders', 'tool_aitest') . "\n\n";
    } else {
        foreach ($providers as $provider) {
            $status = $provider->enabled ? get_string('enabled', 'tool_aitest') : get_string('disabled', 'tool_aitest');
            $diagnostics .= "- {$provider->name} ({$provider->provider}) - {$status}\n";
        }
        $diagnostics .= "\n";
    }
    return $diagnostics;
}

/**
 * Get the active placements diagnostics.
 *
 * @return string The diagnostics string.
 */
function tool_aitest_get_active_placements_diagnostics(): string {
    $url = new moodle_url('/admin/settings.php', ['section' => 'aiplacement']);
    $diagnostics = "## [" . get_string('activeplacements', 'tool_aitest') . "](" . $url->out(false) . ")\n";
    // Use the plugin manager to get all aiplacement plugins.
    $pluginmanager = core\plugin_manager::instance();
    $placements = $pluginmanager->get_plugins_of_type('aiplacement');
    $activeplacements = [];
    foreach ($placements as $component => $plugin) {
        if ($plugin->is_installed_and_upgraded()) {
            $activeplacements[$component] = $plugin;
        }
    }
    if (empty($activeplacements)) {
        $diagnostics .= get_string('noactiveplacements', 'tool_aitest') . "\n\n";
    } else {
        foreach ($activeplacements as $component => $placement) {
            $status = $placement->is_enabled() ? get_string('enabled', 'tool_aitest') : get_string('disabled', 'tool_aitest');
            $diagnostics .= "- {$component} - {$status}\n";
        }
        $diagnostics .= "\n";
    }
    return $diagnostics;
}