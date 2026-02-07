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
 * Landing page for choosing AI testing type
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

require_admin();
$url = new moodle_url('/admin/tool/aitest/index.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo '<div class="card" style="max-width: 600px; margin: 0 auto;">';
echo '<div class="card-header bg-primary text-white">';
echo '<h3 class="mb-0">AI Testing</h3>';
echo '</div>';
echo '<div class="card-body">';
echo '<p class="lead">Select which AI system you would like to test:</p>';
echo '<div class="d-grid gap-3 mt-4">';
echo '<a href="localaimanager.php" class="btn btn-outline-primary btn-lg">';
echo '<strong>Local AI Manager</strong><br>';
echo '<small class="text-muted">Test the local AI manager plugin</small>';
echo '</a>';
echo '<a href="coreai.php" class="btn btn-outline-secondary btn-lg">';
echo '<strong>Core AI</strong><br>';
echo '<small class="text-muted">Test the core Moodle AI system</small>';
echo '</a>';
echo '</div>';
echo '</div>';
echo '</div>';
echo $OUTPUT->footer();
