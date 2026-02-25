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

namespace tool_aitest\reportbuilder\datasource;

use core_reportbuilder\datasource;
use tool_aitest\reportbuilder\local\entities\ai_manager_request_log as ai_manager_request_log_entity;

/**
 * AI Manager Request Log datasource
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_manager_request_log extends datasource {
    public static function get_name(): string {
        return get_string('datasource:aimanagerrequestlog', 'tool_aitest');
    }

    protected function initialise(): void {
        $entity = new ai_manager_request_log_entity();
        $alias = $entity->get_table_alias('local_ai_manager_request_log');
        $this->set_main_table('local_ai_manager_request_log', $alias);
        $this->add_entity($entity);
        $this->add_all_from_entities();
    }

    public function get_default_columns(): array {
        return [
            'ai_manager_request_log:userid',
            'ai_manager_request_log:contextid',
            'ai_manager_request_log:prompttext',
            'ai_manager_request_log:promptcompletion',
            'ai_manager_request_log:requestoptions',
            'ai_manager_request_log:timecreated',
        ];
    }

    public function get_default_filters(): array {
        return [
            'ai_manager_request_log:userid',
            'ai_manager_request_log:prompttext',
        ];
    }

    public function get_default_conditions(): array {
        return [];
    }

    public function get_default_column_sorting(): array {
        return ['ai_manager_request_log:timecreated' => SORT_DESC];
    }
}
