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

namespace tool_aitest\reportbuilder\local\systemreports;

use tool_aitest\reportbuilder\local\entities\ai_manager_request_log;
use core_reportbuilder\system_report;

/**
 * AI Manager Request Log system report
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_manager_request_log_report extends system_report {
    protected function initialise(): void {
        $entity = new ai_manager_request_log();
        $alias = $entity->get_table_alias('local_ai_manager_request_log');
        $this->add_entity($entity);
        $this->set_main_table('local_ai_manager_request_log', $alias);
        $this->add_filters_from_entities([
            'ai_manager_request_log:userid',
            'ai_manager_request_log:prompttext',
        ]);
        $this->add_columns();
    }

    protected function can_view(): bool {
        return true; // Capability can be refined later.
    }

    protected function add_columns(): void {
        $columns = [
            'ai_manager_request_log:userid',
            'ai_manager_request_log:contextid',
            'ai_manager_request_log:prompttext',
            'ai_manager_request_log:promptcompletion',
            'ai_manager_request_log:requestoptions',
            'ai_manager_request_log:timecreated',
        ];
        $this->add_columns_from_entities($columns);
    }
}
