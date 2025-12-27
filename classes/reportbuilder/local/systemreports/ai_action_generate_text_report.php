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

use tool_aitest\reportbuilder\local\entities\ai_action_generate_text;
use core_reportbuilder\system_report;

/**
 * AI Action Generate Text system report
 *
 * This report displays data from the ai_action_generate_text table with no joins.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_action_generate_text_report extends system_report {
    /**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
    protected function initialise(): void {
        $entity = new ai_action_generate_text();
        $alias = $entity->get_table_alias('ai_action_generate_text');
        
        $this->add_entity($entity);
        $this->set_main_table('ai_action_generate_text', $alias);
        
        // Add filters
        $this->add_filters_from_entities([
            'ai_action_generate_text:prompt',
            'ai_action_generate_text:finishreason'
        ]);
        
        // Add columns
        $this->add_columns();
    }

    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return true; // TODO: Add proper capability check if needed
    }

    /**
     * Adds the columns we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_columns(): void {
        $columns = [
            'ai_action_generate_text:prompt',
            'ai_action_generate_text:generatedcontent',
            'ai_action_generate_text:prompttokens',
            'ai_action_generate_text:completiontoken',
            'ai_action_generate_text:totalttokens',
            'ai_action_generate_text:finishreason',
            'ai_action_generate_text:responseid',
            'ai_action_generate_text:fingerprint'
        ];
        
        $this->add_columns_from_entities($columns);
    }
}