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
    /** @var string Request parameter that asks for the test to be run on the settings page. */
    private const RUNPARAM = 'aitestrun';

    /**
     * Inject a "save and run the test" button into the core AI provider action settings form.
     *
     * The core /ai/configure_actions.php form has no plugin hook of its own, so we detect
     * that page in the footer hook and use JavaScript to append a button next to Save.
     *
     * The save is done by core itself: the form carries a returnurl hidden field and core
     * redirects to it after storing the action config. Pointing that field back at this
     * same settings page, with an extra parameter asking for the test, means the admin
     * stays where they were and the test exercises the settings they just saved rather
     * than the ones saved previously.
     *
     * @param \core\hook\output\before_footer_html_generation $hook The footer hook.
     */
    public static function add_provider_test_link(
        \core\hook\output\before_footer_html_generation $hook
    ): void {
        if (!self::is_generate_text_settings_page()) {
            return;
        }

        $data = [
            'url' => self::run_url()->out_as_local_url(false),
            'label' => get_string('saveandruntest', 'tool_aitest'),
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

                    // Core renders returnurl as a hidden field and redirects to it after
                    // saving. Remember the original so an ordinary Save or Cancel still
                    // goes where it always did, even after a failed validation redisplay.
                    var returnurl = form.querySelector('input[name="returnurl"]');
                    if (!returnurl) {
                        returnurl = document.createElement('input');
                        returnurl.type = 'hidden';
                        returnurl.name = 'returnurl';
                        returnurl.value = '';
                        form.appendChild(returnurl);
                    }
                    var original = returnurl.value;
                    var testing = false;
                    var submit = document.getElementById('id_submitbutton');
                    var cancel = document.getElementById('id_cancel');
                    [submit, cancel].forEach(function(el) {
                        if (el) {
                            el.addEventListener('click', function() {
                                // Not for the click we make ourselves below, which is
                                // the one that wants the rewritten returnurl.
                                if (!testing) {
                                    returnurl.value = original;
                                }
                            });
                        }
                    });

                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = d.classes;
                    button.textContent = d.label;
                    button.addEventListener('click', function() {
                        testing = true;
                        returnurl.value = d.url;
                        if (submit) {
                            // Go through the real submit button so client side
                            // validation and any mform submit handlers still run.
                            submit.click();
                        } else {
                            form.submit();
                        }
                    });

                    if (cancel && cancel.parentNode) {
                        cancel.parentNode.insertBefore(button, cancel);
                    } else {
                        var bar = document.getElementById('fgroup_id_buttonar');
                        if (bar) {
                            (bar.querySelector('.felement') || bar).appendChild(button);
                        } else {
                            form.appendChild(button);
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

    /**
     * Run the test and report the outcome on the action settings page.
     *
     * Reached after the "Save and run AI test" button has saved the form and core has
     * redirected back to this page with the run parameter set.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook The top of body hook.
     */
    public static function show_test_result(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE;

        if (!self::is_generate_text_settings_page()) {
            return;
        }
        if (!optional_param(self::RUNPARAM, 0, PARAM_INT) || !confirm_sesskey()) {
            return;
        }
        if (!has_capability('moodle/site:config', \context_system::instance())) {
            return;
        }

        $backurl = new \moodle_url('/ai/configure_actions.php', [
            'provider' => optional_param('provider', '', PARAM_PLUGIN),
            'providerid' => optional_param('providerid', 0, PARAM_INT),
            'action' => \core_ai\aiactions\generate_text::class,
        ]);

        $outcome = tester::run();
        $outcome['detailsurl'] = (new \moodle_url('/admin/tool/aitest/coreai.php', [
            'action' => 'test',
            'returnurl' => $backurl->out_as_local_url(false),
        ]))->out(false);

        // Report it as a notification rather than as top of body HTML, which would put
        // the result above the navbar where it is easily missed. Notifications added
        // here are flushed into the page's notification area, next to the "settings
        // updated" message from the save that ran the test.
        \core\notification::add(
            $PAGE->get_renderer('core')->render_from_template('tool_aitest/testresult', $outcome),
            $outcome['success'] ? \core\notification::SUCCESS : \core\notification::ERROR
        );
    }

    /**
     * Is the current request the core settings form for the Generate text action?
     *
     * @return bool
     */
    private static function is_generate_text_settings_page(): bool {
        // The page URL is set to a synthetic path by core, so match on the real script.
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        if (!str_ends_with($script, '/ai/configure_actions.php')) {
            return false;
        }

        // Only the Generate text action can be exercised by the core AI test.
        return optional_param('action', '', PARAM_RAW) === \core_ai\aiactions\generate_text::class;
    }

    /**
     * This settings page again, asking for the test to be run once it is loaded.
     *
     * @return \moodle_url
     */
    private static function run_url(): \moodle_url {
        return new \moodle_url('/ai/configure_actions.php', [
            'provider' => optional_param('provider', '', PARAM_PLUGIN),
            'providerid' => optional_param('providerid', 0, PARAM_INT),
            'action' => \core_ai\aiactions\generate_text::class,
            self::RUNPARAM => 1,
            'sesskey' => sesskey(),
        ]);
    }
}
