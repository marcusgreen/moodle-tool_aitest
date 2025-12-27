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

namespace tool_aitest\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_reportbuilder\local\filters\{text, number};
use lang_string;

/**
 * AI Action Generate Text entity
 *
 * This entity provides access to the ai_action_generate_text table with no joins.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_action_generate_text extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'ai_action_generate_text' => 'aagt',
        ];
    }

    /**
     * The default machine-readable name for this entity
     *
     * @return string
     */
    protected function get_default_entity_name(): string {
        return 'ai_action_generate_text';
    }

    /**
     * Database tables that this entity uses
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'ai_action_generate_text',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:aiactiongeneratetext', 'tool_aitest');
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this->add_filter($filter);
        }

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $tablealias = $this->get_table_alias('ai_action_generate_text');
        $columns = [];

        // Prompt column
        $columns[] = (new column(
            'prompt',
            new lang_string('prompt', 'tool_aitest'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_LONGTEXT)
            ->add_field("{$tablealias}.prompt")
            ->set_is_sortable(false);

        // Response ID column
        $columns[] = (new column(
            'responseid',
            new lang_string('responseid', 'tool_aitest'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.responseid")
            ->set_is_sortable(true);

        // Fingerprint column
        $columns[] = (new column(
            'fingerprint',
            new lang_string('fingerprint', 'tool_aitest'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.fingerprint")
            ->set_is_sortable(true);

        // Generated content column
        $columns[] = (new column(
            'generatedcontent',
            new lang_string('generatedcontent', 'tool_aitest'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_LONGTEXT)
            ->add_field("{$tablealias}.generatedcontent")
            ->set_is_sortable(false);

        // Finish reason column
        $columns[] = (new column(
            'finishreason',
            new lang_string('finishreason', 'tool_aitest'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_field("{$tablealias}.finishreason")
            ->set_is_sortable(true);

        // Prompt tokens column
        $columns[] = (new column(
            'prompttokens',
            new lang_string('prompttokens', 'tool_aitest'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$tablealias}.prompttokens")
            ->set_is_sortable(true);

        // Completion tokens column
        $columns[] = (new column(
            'completiontoken',
            new lang_string('completiontoken', 'tool_aitest'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("{$tablealias}.completiontoken")
            ->set_is_sortable(true);

        // Total tokens column (calculated)
        $columns[] = (new column(
            'totalttokens',
            new lang_string('totalttokens', 'tool_aitest'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field("({$tablealias}.prompttokens + {$tablealias}.completiontoken)", 'totalttokens')
            ->set_is_sortable(true);

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $tablealias = $this->get_table_alias('ai_action_generate_text');
        $filters = [];

        // Prompt filter
        $filters[] = (new filter(
            text::class,
            'prompt',
            new lang_string('prompt', 'tool_aitest'),
            $this->get_entity_name(),
            "{$tablealias}.prompt"
        ))
            ->add_joins($this->get_joins());

        // Response ID filter
        $filters[] = (new filter(
            text::class,
            'responseid',
            new lang_string('responseid', 'tool_aitest'),
            $this->get_entity_name(),
            "{$tablealias}.responseid"
        ))
            ->add_joins($this->get_joins());

        // Fingerprint filter
        $filters[] = (new filter(
            text::class,
            'fingerprint',
            new lang_string('fingerprint', 'tool_aitest'),
            $this->get_entity_name(),
            "{$tablealias}.fingerprint"
        ))
            ->add_joins($this->get_joins());

        // Finish reason filter
        $filters[] = (new filter(
            text::class,
            'finishreason',
            new lang_string('finishreason', 'tool_aitest'),
            $this->get_entity_name(),
            "{$tablealias}.finishreason"
        ))
            ->add_joins($this->get_joins());

        // Prompt tokens filter
        $filters[] = (new filter(
            number::class,
            'prompttokens',
            new lang_string('prompttokens', 'tool_aitest'),
            $this->get_entity_name(),
            "{$tablealias}.prompttokens"
        ))
            ->add_joins($this->get_joins());

        // Completion tokens filter
        $filters[] = (new filter(
            number::class,
            'completiontoken',
            new lang_string('completiontoken', 'tool_aitest'),
            $this->get_entity_name(),
            "{$tablealias}.completiontoken"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }
}