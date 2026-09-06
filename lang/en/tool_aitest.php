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
 * Strings for component 'tool_aitest', language 'en'
 *
 * @package    tool_aitest
 * @category   string
 * @copyright  2025 2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['pluginname'] = 'AI Test';
$string['runtest'] = 'Run AI test';
$string['saveandruntest'] = 'Save and run AI test';
$string['backtoprovidersettings'] = 'Back to provider settings';
$string['testfailed'] = 'The AI test failed';
$string['fulltestpage'] = 'Open the full AI test page';
$string['general'] = 'General';
$string['custom'] = 'Custom';
$string['testaiservices'] = 'Test AI Services';
$string['testaiconfiguration'] = 'Send a request to the AI System to check if it is working {$a}';
$string['diagnosticsreport'] = 'Diagnostics report';
$string['moodleversion'] = 'Moodle Version';
$string['moodleversioninfo'] = 'Moodle version: {$a->version} ({$a->release})';
$string['activeproviders'] = 'Active AI Providers';
$string['noactiveproviders'] = 'No active AI providers configured.';
$string['activeplacements'] = 'Active AI Placements';
$string['noactiveplacements'] = 'No active AI placements configured.';
$string['enabled'] = 'enabled';
$string['disabled'] = 'disabled';
$string['download_diagnostics'] = 'Download Diagnostics';
$string['sendtestprompt'] = 'Send test prompt';
$string['coreaiheading'] = 'Core AI Testing';
$string['backtochoice'] = 'Back to Choice';
$string['aiproviders'] = 'AI providers';
$string['diagnostics'] = 'Diagnostics';
$string['testpromptsubmitted'] = 'Test prompt submitted successfully!';
$string['messagereturned'] = 'Message returned';
$string['endpointblocked'] = 'Moodle cURL security is blocking the AI provider endpoint(s): {$a->endpoints}. Check the allowed ports (curlsecurityallowedport) and blocked hosts (curlsecurityblockedhosts) under <a href="{$a->url}">Site admin &gt; Server &gt; HTTP security</a>.';
$string['systeminstructionmissing'] = 'The AI provider rejected the request because no system instruction is configured for the text-generation action. Some providers (such as Ollama) require one. Set a <strong>System instruction</strong> value for the Generate text action under <a href="{$a}">Site admin &gt; AI &gt; AI providers</a>, then run the test again.';
$string['actionexception'] = 'The AI request failed: {$a->message}. Review the provider configuration under <a href="{$a->url}">Site admin &gt; AI &gt; AI providers</a>.';
$string['prompttext'] = 'Prompt sent';
$string['responsereceived'] = 'Response received successfully';
$string['responsetext'] = 'Response text';
$string['localaimanagerheading'] = 'Local AI Manager Testing';
$string['connectionsuccessful'] = 'Connection successful!';
$string['response'] = 'Response';
$string['error'] = 'Error';
$string['debuginfo'] = 'Debug Info';
$string['exception'] = 'Exception';
$string['entity:aiactiongeneratetext'] = 'AI Action Generate Text';
$string['datasource:aiactiongeneratetext'] = 'AI Action Generate Text';
$string['report:aiactiongeneratetext'] = 'AI Generate Text Report';
$string['prompt'] = 'Prompt';
$string['responseid'] = 'Response ID';
$string['fingerprint'] = 'Fingerprint';
$string['generatedcontent'] = 'Generated Content';
$string['finishreason'] = 'Finish Reason';
$string['prompttokens'] = 'Prompt Tokens';
$string['completiontoken'] = 'Completion Tokens';
$string['totalttokens'] = 'Total Tokens';
$string['entity:aimanagerrequestlog'] = 'AI Manager Request Log';
$string['datasource:aimanagerrequestlog'] = 'AI Manager Request Log';
$string['report:aimanagerrequestlog'] = 'AI Manager Request Log Report';
$string['userid'] = 'User ID';
$string['contextid'] = 'Context ID';
$string['prompttext'] = 'Prompt Text';
$string['promptcompletion'] = 'Prompt Completion';
$string['requestoptions'] = 'Request Options';
$string['timecreated'] = 'Time Created';

// AI provider presets.
$string['presets'] = 'AI provider presets';
$string['presetsintro'] = 'A preset carries everything needed to configure an AI provider except the parts that are yours: the API key, and anything specific to your account. Pick one, supply those, and the provider is created and tested in a single step.';
$string['presetname'] = 'Preset';
$string['presetdescription'] = 'About this preset';
$string['presetdocs'] = 'Provider documentation';
$string['presetinstall'] = 'Install';
$string['presetinstallheading'] = 'Install preset: {$a}';
$string['presetinstalled'] = 'Provider "{$a}" created';
$string['presetinstancename'] = 'Provider name';
$string['presetinstancename_help'] = 'The name this provider will be listed under on the AI providers page. A number is added if the name is already taken.';
$string['presetenable'] = 'Enable this provider now';
$string['presetenable_desc'] = 'Leave this off to create the provider without putting it into service. The test below runs either way, so you can confirm it works before enabling it.';
$string['presetactionsettings'] = 'Settings applied to {$a}';
$string['presetfieldexample'] = 'For example: {$a}';
$string['presetopensettings'] = 'Open provider settings';
$string['presetbacktolist'] = 'Back to presets';
$string['presetneeds50'] = 'Installing presets requires Moodle 5.0 or later, which is the first version where several AI providers can be configured side by side. Testing and diagnostics work on this version as normal.';
$string['presetneedsbranch'] = 'Requires Moodle {$a}';
$string['presetprovidermissing'] = 'Provider plugin {$a} is not installed';
$string['presetnotinstallable'] = 'This preset cannot be installed on this site: {$a}';
$string['presetnoprocessor'] = 'Provider {$a} has no text generation processor, so it cannot be tested this way.';
$string['presetmissingfield'] = 'A preset is missing its {$a}.';
$string['presetunreadable'] = 'Preset file {$a} could not be read.';
$string['presetbadjson'] = 'Preset file {$a->file} is not valid JSON: {$a->message}';
$string['presetbadformatversion'] = 'Preset file {$a->file} declares format version {$a->found}, but this plugin understands version {$a->supported} and below.';
$string['presetlink'] = 'Install a ready made AI provider configuration and test it {$a}';
$string['endpointunreachable'] = 'The AI provider did not return a usable response. This usually means the endpoint address is wrong, the host cannot be reached from this server, or something other than the provider\'s API answered. Check the endpoint under <a href="{$a}">Site admin &gt; AI &gt; AI providers</a>, then run the test again.';
