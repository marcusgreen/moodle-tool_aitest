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
 * Install a ready made AI provider configuration and test it in one step.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

require_admin();

use tool_aitest\form\preset_install_form;
use tool_aitest\local\installer;
use tool_aitest\local\preset;

$actionparam = optional_param('action', '', PARAM_ALPHA);
$presetid = optional_param('preset', '', PARAM_ALPHANUMEXT);

$url = new moodle_url('/admin/tool/aitest/presets.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('presets', 'tool_aitest'));
$PAGE->set_heading($SITE->fullname);

$output = $PAGE->get_renderer('core');
$presets = preset::get_bundled();

// Install one preset: ask for the secrets it cannot carry, create the instance, test it.
if ($actionparam === 'install' && isset($presets[$presetid])) {
    $preset = $presets[$presetid];

    if (!$preset->is_installable()) {
        throw new moodle_exception('presetnotinstallable', 'tool_aitest', $url->out(false),
            $preset->get_unavailable_reason());
    }

    // The preset being installed travels in the form's action URL: it is what the page
    // was reached by, and it survives a validation redisplay without a hidden field.
    $formurl = new moodle_url($url, ['action' => 'install', 'preset' => $preset->id]);
    $form = new preset_install_form($formurl->out(false), ['preset' => $preset]);

    if ($form->is_cancelled()) {
        redirect($url);
    }

    if ($data = $form->get_data()) {
        $provider = installer::install(
            preset: $preset,
            name: $data->name,
            values: $form->get_config_values(),
            enabled: !empty($data->enabled),
            actionvalues: $form->get_action_values(),
        );

        // Test the instance that was just created rather than whatever the AI subsystem
        // would have picked, so the result describes this preset and nothing else.
        $outcome = \tool_aitest\tester::run_for_provider($provider);
        $outcome['detailsurl'] = (new moodle_url('/admin/tool/aitest/coreai.php', ['action' => 'test']))->out(false);

        echo $output->header();
        echo $output->heading(get_string('presetinstalled', 'tool_aitest', $provider->name));
        echo $output->render_from_template('tool_aitest/testresult', $outcome);
        echo html_writer::div(
            html_writer::link(
                new moodle_url('/ai/configure.php', ['id' => $provider->id]),
                get_string('presetopensettings', 'tool_aitest'),
                ['class' => 'btn btn-secondary']
            )
            . ' '
            . html_writer::link($url, get_string('presetbacktolist', 'tool_aitest'), ['class' => 'btn btn-link']),
            'mt-3'
        );
        echo $output->footer();
        exit;
    }

    echo $output->header();
    echo $output->heading(get_string('presetinstallheading', 'tool_aitest', $preset->name));
    $form->display();
    echo $output->footer();
    exit;
}

// The list.
$rows = [];
foreach ($presets as $preset) {
    $rows[] = [
        'name' => $preset->name,
        'description' => $preset->description,
        'provider' => $preset->get_provider_display_name(),
        'docsurl' => $preset->docsurl,
        'hasdocs' => $preset->docsurl !== '',
        'installable' => $preset->is_installable(),
        'reason' => $preset->get_unavailable_reason(),
        'installurl' => (new moodle_url($url, ['action' => 'install', 'preset' => $preset->id]))->out(false),
    ];
}

echo $output->header();
echo $output->render_from_template('tool_aitest/presets', [
    'heading' => get_string('presets', 'tool_aitest'),
    'intro' => get_string('presetsintro', 'tool_aitest'),
    'supported' => installer::is_supported(),
    'unsupportedmessage' => get_string('presetneeds50', 'tool_aitest'),
    'presets' => $rows,
    'hasrows' => (bool) $rows,
    'testurl' => (new moodle_url('/admin/tool/aitest/coreai.php'))->out(false),
    'providersurl' => (new moodle_url('/ai/configure.php'))->out(false),
]);
echo $output->footer();
