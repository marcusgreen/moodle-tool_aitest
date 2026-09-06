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
 * Runs a generate_text request through the AI subsystem and reports what happened.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tester {
    /** @var string The prompt sent to the provider. */
    public const PROMPT = 'Please respond to confirm I been successfull in connecting to you and return nothing else';

    /**
     * Send the test prompt and describe the outcome.
     *
     * @return array Keys: success (bool), message (string, HTML), responsetext (string),
     *               result (string, var_dump of the action result), prompttext (string).
     */
    public static function run(): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $outcome = self::new_outcome();
        $action = self::new_action();

        $blocked = self::blocked_endpoints();
        if ($blocked) {
            return self::describe_blocked_endpoints($blocked, $outcome);
        }

        try {
            $result = self::get_manager()->process_action($action);
        } catch (\Throwable $e) {
            return self::describe_exception($e, $outcome);
        }

        return self::describe_response($result, $outcome);
    }

    /**
     * Send the test prompt to one named provider instance.
     *
     * process_action() walks every enabled provider in order and returns the first that
     * answers, which is no use for confirming that a particular instance works: a
     * different provider may well be the one that replied. Core reaches the right
     * processor through manager::call_action_provider(), which is protected, so do what
     * it does. It is three lines and it is the only way to test an instance that has
     * deliberately been left disabled.
     *
     * @param \core_ai\provider $provider The instance to test.
     * @return array Same shape as {@see self::run()}.
     */
    public static function run_for_provider(\core_ai\provider $provider): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $outcome = self::new_outcome();
        $action = self::new_action();

        $blocked = self::blocked_endpoints();
        if ($blocked) {
            return self::describe_blocked_endpoints($blocked, $outcome);
        }

        $processclass = '\\' . $provider->get_name() . '\\process_generate_text';
        if (!class_exists($processclass)) {
            $outcome['message'] = get_string('presetnoprocessor', 'tool_aitest', $provider->get_name());
            return $outcome;
        }

        try {
            $processor = new $processclass($provider, $action);
            $result = $processor->process();
        } catch (\Throwable $e) {
            return self::describe_exception($e, $outcome);
        }

        return self::describe_response($result, $outcome);
    }

    /**
     * The AI manager, however this Moodle wants it built.
     *
     * 5.0 injects the database into the manager; 4.5 does not take it. Ask the
     * constructor rather than the release string, so a branch this code predates is
     * judged on what it actually needs.
     *
     * @return \core_ai\manager
     */
    private static function get_manager(): \core_ai\manager {
        global $DB;

        $constructor = (new \ReflectionClass(\core_ai\manager::class))->getConstructor();
        if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
            return new \core_ai\manager($DB);
        }

        return new \core_ai\manager();
    }

    /**
     * The generate_text action carrying the test prompt.
     *
     * @return \core_ai\aiactions\generate_text
     */
    private static function new_action(): \core_ai\aiactions\generate_text {
        global $USER;

        return new \core_ai\aiactions\generate_text(
            contextid: \context_system::instance()->id,
            userid: $USER->id,
            prompttext: self::PROMPT
        );
    }

    /**
     * An outcome array describing nothing having happened yet.
     *
     * @return array
     */
    private static function new_outcome(): array {
        return [
            'success' => false,
            'message' => '',
            'responsetext' => '',
            'result' => '',
            'prompttext' => self::PROMPT,
        ];
    }

    /**
     * Fill in an outcome from the provider's response.
     *
     * @param \core_ai\aiactions\responses\response_base $result The response.
     * @param array $outcome The outcome so far.
     * @return array The completed outcome.
     */
    private static function describe_response($result, array $outcome): array {
        ob_start();
        var_dump($result);
        $outcome['result'] = ob_get_clean();

        if ($result->get_success()) {
            $outcome['success'] = true;
            $outcome['responsetext'] = $result->get_response_data()['generatedcontent'];
        } else {
            $outcome['message'] = $result->get_errormessage();
        }

        return $outcome;
    }

    /**
     * Turn an exception from the AI subsystem into something an admin can act on.
     *
     * @param \Throwable $e The exception.
     * @param array $outcome The outcome so far.
     * @return array The completed outcome.
     */
    private static function describe_exception(\Throwable $e, array $outcome): array {
        $configurl = (new \moodle_url('/admin/settings.php', ['section' => 'aiprovider']))->out(false);

        if (str_contains($e->getMessage(), 'Error code and message must exist')) {
            // The HTTP request never got a usable response - typically an endpoint that
            // does not resolve, or one that answered with something that is not the
            // provider's API - so core cannot map it to an AI error and throws instead.
            $outcome['message'] = get_string('endpointunreachable', 'tool_aitest', $configurl);
        } else if (str_contains($e->getMessage(), 'get_system_instruction')) {
            // The provider action has no system instruction configured, which some
            // providers (e.g. Ollama) require. Give a clear pointer instead of a raw TypeError.
            $outcome['message'] = get_string('systeminstructionmissing', 'tool_aitest', $configurl);
        } else {
            $outcome['message'] = get_string(
                'actionexception',
                'tool_aitest',
                (object) ['message' => s($e->getMessage()), 'url' => $configurl]
            );
        }

        return $outcome;
    }

    /**
     * Report endpoints that Moodle's own cURL security would refuse to call.
     *
     * @param string[] $blocked Descriptions of the blocked endpoints.
     * @param array $outcome The outcome so far.
     * @return array The completed outcome.
     */
    private static function describe_blocked_endpoints(array $blocked, array $outcome): array {
        $outcome['message'] = get_string(
            'endpointblocked',
            'tool_aitest',
            (object) [
                'endpoints' => implode(', ', $blocked),
                'url' => (new \moodle_url('/admin/settings.php', ['section' => 'httpsecurity']))->out(false),
            ]
        );

        return $outcome;
    }

    /**
     * Detect enabled provider endpoints that Moodle's own cURL security would block.
     *
     * A block makes the underlying request fail with code 0, which core cannot map to an
     * AI error and instead throws "Invalid error code: 0". Reporting it here gives a clear
     * message before that happens.
     *
     * @return string[] Descriptions of the blocked endpoints, empty if none.
     */
    private static function blocked_endpoints(): array {
        global $DB;

        $helper = new \core\files\curl_security_helper();
        if (!$helper->is_enabled()) {
            return [];
        }

        $blocked = [];
        $providers = $DB->get_records('ai_providers', ['enabled' => 1]);
        foreach ($providers as $provider) {
            $config = json_decode($provider->config);
            if (empty($config->endpoint)) {
                continue;
            }
            if ($helper->url_is_blocked($config->endpoint)) {
                $blocked[] = $provider->name . ' (' . $config->endpoint . ')';
            }
        }

        return $blocked;
    }
}
