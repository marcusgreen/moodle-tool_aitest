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

namespace tool_aitest;

/**
 * Hook callbacks for tool_aitest.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Inject a "run the test" link into the core AI provider action settings form.
     *
     * The core /ai/configure_actions.php form has no plugin hook of its own, so we
     * detect that page in the footer hook and use JavaScript to append a link that
     * opens the tool_aitest core AI test.
     *
     * @param \core\hook\output\before_footer_html_generation $hook The footer hook.
     */
    public static function add_provider_test_link(
        \core\hook\output\before_footer_html_generation $hook
    ): void {
        // The page URL is set to a synthetic path by core, so match on the real script.
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        if (!str_ends_with($script, '/ai/configure_actions.php')) {
            return;
        }

        // Only the Generate text action can be exercised by the core AI test.
        if (optional_param('action', '', PARAM_RAW) !== \core_ai\aiactions\generate_text::class) {
            return;
        }

        $testurl = new \moodle_url('/admin/tool/aitest/coreai.php', [
            'action' => 'test',
            'provider' => optional_param('provider', '', PARAM_PLUGIN),
            'providerid' => optional_param('providerid', 0, PARAM_INT),
        ]);

        $data = [
            'url' => $testurl->out(false),
            'label' => get_string('runtest', 'tool_aitest'),
            'classes' => 'btn btn-info ml-2 mr-2 tool-aitest-runtest',
        ];

        $js = <<<JS
            (function() {
                var d = %s;
                function inject() {
                    var form = document.querySelector('form.mform');
                    if (!form || form.querySelector('.tool-aitest-runtest')) {
                        return;
                    }
                    var link = document.createElement('a');
                    link.href = d.url;
                    link.className = d.classes;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.textContent = d.label;
                    var cancel = document.getElementById('id_cancel');
                    if (cancel && cancel.parentNode) {
                        cancel.parentNode.insertBefore(link, cancel);
                    } else {
                        var bar = document.getElementById('fgroup_id_buttonar');
                        if (bar) {
                            (bar.querySelector('.felement') || bar).appendChild(link);
                        } else {
                            form.appendChild(link);
                        }
                    }
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', inject);
                } else {
                    inject();
                }
            })();
            JS;

        $hook->add_html(\html_writer::script(sprintf($js, json_encode($data))));
    }
}
