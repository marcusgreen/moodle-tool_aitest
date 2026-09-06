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

namespace tool_aitest\form;

use tool_aitest\local\preset;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Asks for the few things a preset cannot ship: the API key, and anything else secret.
 *
 * Everything the preset already knows - endpoints, model, system instruction, extra
 * parameters - is shown read only, so the admin can see what is about to be written
 * without being asked to retype it.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preset_install_form extends \moodleform {
    #[\Override]
    protected function definition(): void {
        $mform = $this->_form;
        /** @var preset $preset */
        $preset = $this->_customdata['preset'];

        $mform->addElement('hidden', 'preset', $preset->id);
        $mform->setType('preset', PARAM_ALPHANUMEXT);

        if ($preset->description !== '') {
            $mform->addElement('static', 'presetdescription', get_string('presetdescription', 'tool_aitest'),
                format_text($preset->description, FORMAT_PLAIN));
        }

        $mform->addElement('text', 'name', get_string('presetinstancename', 'tool_aitest'), 'size="40"');
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $preset->name);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('name', 'presetinstancename', 'tool_aitest');

        foreach ($preset->config as $key => $default) {
            $this->add_config_field($preset, $key, $default);
        }

        foreach ($preset->get_mandatory_action_fields() as $field) {
            $this->add_action_field($preset, $field);
        }

        $this->add_action_summary($preset);

        $mform->addElement('advcheckbox', 'enabled', get_string('presetenable', 'tool_aitest'),
            get_string('presetenable_desc', 'tool_aitest'));
        // Default to disabled whatever the preset suggests: an untested key should not
        // be able to take over live AI traffic through the provider ordering, and the
        // install runs the test immediately anyway.
        $mform->setDefault('enabled', 0);

        $this->add_action_buttons(true, get_string('presetinstall', 'tool_aitest'));
    }

    /**
     * Add one instance config field.
     *
     * @param preset $preset The preset being installed.
     * @param string $key The config key.
     * @param mixed $default The preset's value: null means the admin must supply it.
     */
    private function add_config_field(preset $preset, string $key, mixed $default): void {
        $mform = $this->_form;
        $label = $this->field_label($preset, $key);
        $type = $preset->is_secret_field($key) ? 'passwordunmask' : 'text';

        $mform->addElement($type, "config_{$key}", $label, 'size="48"');
        $mform->setType("config_{$key}", PARAM_RAW_TRIMMED);

        if ($default !== null) {
            $mform->setDefault("config_{$key}", $default);
        }
        if (in_array($key, $preset->get_mandatory_fields(), true)) {
            $mform->addRule("config_{$key}", get_string('required'), 'required', null, 'client');
        }

        $help = $preset->get_field_help($key);
        $placeholder = $preset->get_placeholder($key);
        if ($placeholder !== '') {
            $help = trim($help . ' ' . get_string('presetfieldexample', 'tool_aitest', $placeholder));
        }
        if ($help !== '') {
            $mform->addElement('static', "config_{$key}_help", '', \html_writer::tag('small', s($help)));
        }
    }

    /**
     * Add one mandatory action setting, such as an Azure deployment name.
     *
     * @param preset $preset The preset being installed.
     * @param array $field One entry from preset::get_mandatory_action_fields().
     */
    private function add_action_field(preset $preset, array $field): void {
        $mform = $this->_form;
        $name = "setting{$field['index']}_{$field['key']}";

        $mform->addElement('text', $name, $this->field_label($preset, $field['key']), 'size="48"');
        $mform->setType($name, PARAM_RAW_TRIMMED);
        $mform->addRule($name, get_string('required'), 'required', null, 'client');

        $help = $preset->get_field_help($field['key']);
        $placeholder = $preset->get_placeholder($field['key']);
        if ($placeholder !== '') {
            $help = trim($help . ' ' . get_string('presetfieldexample', 'tool_aitest', $placeholder));
        }
        if ($help !== '') {
            $mform->addElement('static', "{$name}_help", '', \html_writer::tag('small', s($help)));
        }
    }

    /**
     * Show, without asking about, the settings the preset already carries.
     *
     * @param preset $preset The preset being installed.
     */
    private function add_action_summary(preset $preset): void {
        $mform = $this->_form;

        foreach ($preset->actions as $actionclass => $action) {
            $settings = $action['settings'] ?? [];
            if (!is_array($settings) || !array_filter($settings, fn($value) => $value !== null)) {
                continue;
            }

            $rows = [];
            foreach ($settings as $key => $value) {
                if ($value === null) {
                    // Asked for on this form rather than shipped by the preset.
                    continue;
                }
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $rows[] = \html_writer::tag('dt', s($key))
                    . \html_writer::tag('dd', \html_writer::tag('code', s((string) $value)));
            }

            $mform->addElement(
                'static',
                'summary_' . md5((string) $actionclass),
                get_string('presetactionsettings', 'tool_aitest', $this->action_name($actionclass)),
                \html_writer::tag('dl', implode('', $rows), ['class' => 'row']),
            );
        }
    }

    /**
     * A label for a config field, preferring the provider plugin's own wording.
     *
     * @param preset $preset The preset being installed.
     * @param string $key The config key.
     * @return string
     */
    private function field_label(preset $preset, string $key): string {
        if (isset($preset->fields[$key]['label'])) {
            return (string) $preset->fields[$key]['label'];
        }
        // The provider plugin names these fields already; reuse its strings so the
        // install form and the core settings form call the same field by the same name.
        $manager = get_string_manager();
        if ($manager->string_exists($key, $preset->plugin)) {
            return get_string($key, $preset->plugin);
        }

        return $key;
    }

    /**
     * The display name of an action, falling back to its class name.
     *
     * @param string $actionclass The action class name.
     * @return string
     */
    private function action_name(string $actionclass): string {
        $actionclass = ltrim($actionclass, '\\');

        return class_exists($actionclass) ? $actionclass::get_name() : $actionclass;
    }

    #[\Override]
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        /** @var preset $preset */
        $preset = $this->_customdata['preset'];

        foreach ($preset->get_mandatory_fields() as $key) {
            if (trim((string) ($data["config_{$key}"] ?? '')) === '') {
                $errors["config_{$key}"] = get_string('required');
            }
        }

        foreach ($preset->get_mandatory_action_fields() as $field) {
            $name = "setting{$field['index']}_{$field['key']}";
            if (trim((string) ($data[$name] ?? '')) === '') {
                $errors[$name] = get_string('required');
            }
        }

        return $errors;
    }

    /**
     * The config values the admin supplied, stripped of the form's field prefix.
     *
     * @return array Keyed as the preset's config is, or empty if the form was not submitted.
     */
    public function get_config_values(): array {
        $data = $this->get_data();
        if (!$data) {
            return [];
        }

        $values = [];
        foreach ((array) $data as $key => $value) {
            if (str_starts_with($key, 'config_') && !str_ends_with($key, '_help')) {
                $values[substr($key, strlen('config_'))] = $value;
            }
        }

        return $values;
    }

    /**
     * The action settings the admin supplied.
     *
     * @return array Action class name => [setting => value], empty if not submitted.
     */
    public function get_action_values(): array {
        $data = $this->get_data();
        if (!$data) {
            return [];
        }

        /** @var preset $preset */
        $preset = $this->_customdata['preset'];
        $values = [];
        foreach ($preset->get_mandatory_action_fields() as $field) {
            $name = "setting{$field['index']}_{$field['key']}";
            if (isset($data->$name)) {
                $values[$field['action']][$field['key']] = $data->$name;
            }
        }

        return $values;
    }
}
