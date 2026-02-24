<?php
// This file is part of Moodle - https://moodle.org/
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
 * AI Action Generate Text Report
 *
 * This report displays data from the ai_action_generate_text table with no joins to other tables.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');


require_login();

defined('MOODLE_INTERNAL') || die();

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/admin/tool/aitest/report/ai_action_generate_text.php'));
$PAGE->set_title(get_string('report:aiactiongeneratetext', 'tool_aitest'));
$PAGE->navbar->add(get_string('report:aiactiongeneratetext', 'tool_aitest'), $PAGE->url);

// Check capability
require_capability('moodle/site:config', context_system::instance());

echo $OUTPUT->header();
echo '<a href="../localaimanager.php" class="btn btn-secondary mb-3">Back to Local AI Manager</a>';
// Create and output the report
$report = \core_reportbuilder\system_report_factory::create(
    \tool_aitest\reportbuilder\local\systemreports\ai_action_generate_text_report::class,
    context_system::instance()
);

echo $report->output();
echo $OUTPUT->footer();