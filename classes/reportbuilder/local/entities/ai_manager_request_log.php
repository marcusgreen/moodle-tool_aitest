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
 * AI Manager Request Log entity
 *
 * Provides access to the local_ai_manager_request_log table.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_aitest\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_reportbuilder\local\filters\{text, number};
use lang_string;

class ai_manager_request_log extends base {
    protected function get_default_table_aliases(): array {
        return ['local_ai_manager_request_log' => 'lrl'];
    }

    protected function get_default_entity_name(): string {
        return 'ai_manager_request_log';
    }

    protected function get_default_tables(): array {
        return ['local_ai_manager_request_log'];
    }

    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:aimanagerrequestlog', 'tool_aitest');
    }

    public function initialise(): base {
        foreach ($this->get_all_columns() as $col) {
            $this->add_column($col);
        }
        foreach ($this->get_all_filters() as $fil) {
            $this->add_filter($fil);
        }
        return $this;
    }

    protected function get_all_columns(): array {
        $alias = $this->get_table_alias('local_ai_manager_request_log');
        $columns = [];

        $columns[] = (new column('userid', new lang_string('userid', 'tool_aitest'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.userid");

        $columns[] = (new column('contextid', new lang_string('contextid', 'tool_aitest'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.contextid");

        $columns[] = (new column('prompttext', new lang_string('prompttext', 'tool_aitest'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_LONGTEXT)
            ->add_field("{$alias}.prompttext");

        $columns[] = (new column('promptcompletion', new lang_string('promptcompletion', 'tool_aitest'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_LONGTEXT)
            ->add_field("{$alias}.promptcompletion");

        $columns[] = (new column('requestoptions', new lang_string('requestoptions', 'tool_aitest'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$alias}.requestoptions");

        $columns[] = (new column('timecreated', new lang_string('timecreated', 'tool_aitest'), $this->get_entity_name()))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$alias}.timecreated");

        return $columns;
    }

    protected function get_all_filters(): array {
        $alias = $this->get_table_alias('local_ai_manager_request_log');
        $filters = [];
        $filters[] = (new filter(text::class, 'prompttext', new lang_string('prompttext', 'tool_aitest'), $this->get_entity_name(), "{$alias}.prompttext"))
            ->add_joins($this->get_joins());
        $filters[] = (new filter(text::class, 'promptcompletion', new lang_string('promptcompletion', 'tool_aitest'), $this->get_entity_name(), "{$alias}.promptcompletion"))
            ->add_joins($this->get_joins());
        $filters[] = (new filter(number::class, 'userid', new lang_string('userid', 'tool_aitest'), $this->get_entity_name(), "{$alias}.userid"))
            ->add_joins($this->get_joins());
        $filters[] = (new filter(number::class, 'timecreated', new lang_string('timecreated', 'tool_aitest'), $this->get_entity_name(), "{$alias}.timecreated"))
            ->add_joins($this->get_joins());
        return $filters;
    }
}
