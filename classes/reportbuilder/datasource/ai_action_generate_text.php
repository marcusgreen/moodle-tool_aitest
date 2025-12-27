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
use tool_aitest\reportbuilder\local\entities\ai_action_generate_text as ai_action_generate_text_entity;

/**
 * AI Action Generate Text datasource
 *
 * This datasource provides access to the ai_action_generate_text table with no joins to other tables.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_action_generate_text extends datasource {

    /**
     * Return user friendly name of the datasource
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('datasource:aiactiongeneratetext', 'tool_aitest');
    }

    /**
     * Initialise report
     */
    protected function initialise(): void {
        // Add our custom entity - this uses only the ai_action_generate_text table with no joins
        $entity = new ai_action_generate_text_entity();
        $tablealias = $entity->get_table_alias('ai_action_generate_text');

        $this->set_main_table('ai_action_generate_text', $tablealias);
        $this->add_entity($entity);

        // Add all columns and filters from the entity
        $this->add_all_from_entities();
    }

    /**
     * Return the columns that will be added to the report upon creation
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'ai_action_generate_text:prompt',
            'ai_action_generate_text:generatedcontent',
            'ai_action_generate_text:prompttokens',
            'ai_action_generate_text:completiontoken',
            'ai_action_generate_text:totalttokens',
            'ai_action_generate_text:finishreason',
        ];
    }

    /**
     * Return the filters that will be added to the report upon creation
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [
            'ai_action_generate_text:prompt',
            'ai_action_generate_text:finishreason',
            'ai_action_generate_text:prompttokens',
        ];
    }

    /**
     * Return the conditions that will be added to the report upon creation
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }

    /**
     * Return the default sorting that will be added to the report once it is created
     *
     * @return array|int[]
     */
    public function get_default_column_sorting(): array {
        return [
            'ai_action_generate_text:prompttokens' => SORT_DESC,
        ];
    }
}