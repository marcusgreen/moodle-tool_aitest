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

namespace tool_aitest\local;

/**
 * One AI provider preset: everything needed to configure a provider except its secrets.
 *
 * A preset is deliberately dumb data. It knows what provider plugin it is for, what
 * belongs in that provider's instance config, and what belongs in each action's
 * settings; it does not know how the running Moodle stores any of that. See
 * {@see installer} for the part that writes.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preset {
    /** @var int The preset file format this code understands. */
    public const FORMATVERSION = 1;

    /**
     * @param string $id Stable identifier, unique within a file.
     * @param string $name Human readable name, used as the provider instance name.
     * @param string $description What this preset is for.
     * @param string $docsurl Provider documentation, shown as a link. May be empty.
     * @param string $plugin Provider plugin name, e.g. 'aiprovider_openai'.
     * @param bool $enabled Whether the preset suggests enabling the instance.
     * @param array $config Instance config. A null value means "mandatory, ask the admin".
     * @param array $fields Presentation metadata for config keys: placeholder, help, secret.
     * @param array $actions Action class name => ['enabled' => bool, 'settings' => array].
     * @param string $requiresbranch Minimum Moodle branch, e.g. '501'. Empty for any.
     */
    private function __construct(
        /** @var string Stable identifier, unique within a file. */
        public readonly string $id,
        /** @var string Human readable name, used as the provider instance name. */
        public readonly string $name,
        /** @var string What this preset is for. */
        public readonly string $description,
        /** @var string Provider documentation URL. */
        public readonly string $docsurl,
        /** @var string Provider plugin name. */
        public readonly string $plugin,
        /** @var bool Whether the preset suggests enabling the instance. */
        public readonly bool $enabled,
        /** @var array Instance config, null values being mandatory fields. */
        public readonly array $config,
        /** @var array Presentation metadata for config keys. */
        public readonly array $fields,
        /** @var array Action class name => ['enabled' => bool, 'settings' => array]. */
        public readonly array $actions,
        /** @var string Minimum Moodle branch, empty for any. */
        public readonly string $requiresbranch,
    ) {
    }

    /**
     * Build a preset from one decoded entry of a preset file.
     *
     * @param array $data The decoded preset.
     * @return self
     * @throws \moodle_exception If the entry is missing something it cannot do without.
     */
    public static function from_array(array $data): self {
        foreach (['id', 'name', 'provider'] as $required) {
            if (empty($data[$required]) || !is_string($data[$required])) {
                throw new \moodle_exception('presetmissingfield', 'tool_aitest', '', $required);
            }
        }

        return new self(
            id: clean_param($data['id'], PARAM_ALPHANUMEXT),
            name: clean_param($data['name'], PARAM_TEXT),
            description: clean_param($data['description'] ?? '', PARAM_TEXT),
            docsurl: clean_param($data['docsurl'] ?? '', PARAM_URL),
            plugin: clean_param($data['provider'], PARAM_PLUGIN),
            enabled: !empty($data['enabled']),
            config: is_array($data['config'] ?? null) ? $data['config'] : [],
            fields: is_array($data['fields'] ?? null) ? $data['fields'] : [],
            actions: is_array($data['actions'] ?? null) ? $data['actions'] : [],
            requiresbranch: (string) ($data['requires']['moodlebranch'] ?? ''),
        );
    }

    /**
     * Every preset shipped with this plugin, in file name order.
     *
     * Presets for provider plugins that are not installed on this site are still
     * returned: the list page shows them greyed out, which is more use to an admin
     * than silently having fewer options than the documentation describes.
     *
     * @return self[] Keyed by preset id.
     */
    public static function get_bundled(): array {
        // Relative to this file rather than to $CFG->dirroot, so the plugin does not
        // care where in the tree it was installed.
        $dir = __DIR__ . '/../../presets';

        $presets = [];
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            try {
                foreach (self::from_file($file) as $preset) {
                    $presets[$preset->id] = $preset;
                }
            } catch (\moodle_exception $e) {
                // One unreadable file should not take the whole list down with it.
                debugging('tool_aitest: ignoring preset file ' . basename($file) . ': ' . $e->getMessage(),
                    DEBUG_DEVELOPER);
            }
        }

        return $presets;
    }

    /**
     * Read and validate one preset file.
     *
     * @param string $file Absolute path to the JSON file.
     * @return self[]
     * @throws \moodle_exception If the file is not valid JSON, or is a format we do not know.
     */
    public static function from_file(string $file): array {
        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new \moodle_exception('presetunreadable', 'tool_aitest', '', basename($file));
        }

        return self::from_json($contents, basename($file));
    }

    /**
     * Decode a preset file's contents.
     *
     * @param string $json The file contents.
     * @param string $filename Used in error messages only.
     * @return self[]
     * @throws \moodle_exception If the JSON is malformed or the format version is not supported.
     */
    public static function from_json(string $json, string $filename = ''): array {
        try {
            $data = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \moodle_exception('presetbadjson', 'tool_aitest', '',
                (object) ['file' => $filename, 'message' => $e->getMessage()]);
        }

        $formatversion = (int) ($data['formatversion'] ?? 0);
        if ($formatversion < 1 || $formatversion > self::FORMATVERSION) {
            throw new \moodle_exception('presetbadformatversion', 'tool_aitest', '',
                (object) ['file' => $filename, 'found' => $formatversion, 'supported' => self::FORMATVERSION]);
        }

        $presets = [];
        foreach (($data['presets'] ?? []) as $entry) {
            if (is_array($entry)) {
                $presets[] = self::from_array($entry);
            }
        }

        return $presets;
    }

    /**
     * The provider class this preset creates an instance of.
     *
     * @return string Fully qualified class name, e.g. 'aiprovider_openai\provider'.
     */
    public function get_provider_class(): string {
        return "\\{$this->plugin}\\provider";
    }

    /**
     * Is the provider plugin this preset needs present on this site?
     *
     * @return bool
     */
    public function provider_installed(): bool {
        return class_exists($this->get_provider_class());
    }

    /**
     * Is this Moodle new enough for the preset's provider?
     *
     * @return bool
     */
    public function branch_supported(): bool {
        global $CFG;

        return $this->requiresbranch === '' || (int) $CFG->branch >= (int) $this->requiresbranch;
    }

    /**
     * Can this preset be installed on this site right now?
     *
     * @return bool
     */
    public function is_installable(): bool {
        return $this->provider_installed() && $this->branch_supported() && installer::is_supported();
    }

    /**
     * Why this preset cannot be installed, for display in the list.
     *
     * @return string Empty if it can be installed.
     */
    public function get_unavailable_reason(): string {
        if (!installer::is_supported()) {
            return get_string('presetneeds50', 'tool_aitest');
        }
        if (!$this->branch_supported()) {
            return get_string('presetneedsbranch', 'tool_aitest', $this->format_branch());
        }
        if (!$this->provider_installed()) {
            return get_string('presetprovidermissing', 'tool_aitest', $this->plugin);
        }
        return '';
    }

    /**
     * The provider plugin's display name, falling back to its component name.
     *
     * @return string
     */
    public function get_provider_display_name(): string {
        $manager = \core_plugin_manager::instance();
        $info = $manager->get_plugin_info($this->plugin);

        return $info ? $info->displayname : $this->plugin;
    }

    /**
     * Config keys the admin has to supply, being those the preset left null.
     *
     * @return string[]
     */
    public function get_mandatory_fields(): array {
        $mandatory = [];
        foreach ($this->config as $key => $value) {
            if ($value === null || !empty($this->fields[$key]['required'])) {
                $mandatory[] = $key;
            }
        }

        return $mandatory;
    }

    /**
     * Action settings the admin has to supply, being those the preset left null.
     *
     * Not every mandatory value belongs to the provider instance. Azure's deployment
     * name and AWS Bedrock's model id are per action and per site, so a preset can no
     * more ship them than it can ship an API key.
     *
     * @return array List of ['action' => class name, 'key' => setting name, 'index' => int].
     */
    public function get_mandatory_action_fields(): array {
        $mandatory = [];
        $index = 0;
        foreach ($this->actions as $actionclass => $action) {
            $settings = is_array($action['settings'] ?? null) ? $action['settings'] : [];
            foreach ($settings as $key => $value) {
                if ($value === null) {
                    $mandatory[] = [
                        'action' => ltrim((string) $actionclass, '\\'),
                        'key' => $key,
                        'index' => $index++,
                    ];
                }
            }
        }

        return $mandatory;
    }

    /**
     * Should this config key be entered, and stored, as a secret?
     *
     * @param string $key The config key.
     * @return bool
     */
    public function is_secret_field(string $key): bool {
        if (isset($this->fields[$key]['secret'])) {
            return (bool) $this->fields[$key]['secret'];
        }

        // Providers vary in what they call their secrets: AWS Bedrock has two, and
        // Ollama's is a basic auth password rather than a key.
        return in_array($key, ['apikey', 'apisecret', 'password'], true);
    }

    /**
     * Placeholder text for a config key, if the preset supplies one.
     *
     * @param string $key The config key.
     * @return string
     */
    public function get_placeholder(string $key): string {
        return (string) ($this->fields[$key]['placeholder'] ?? '');
    }

    /**
     * Help text for a config key, if the preset supplies any.
     *
     * @param string $key The config key.
     * @return string
     */
    public function get_field_help(string $key): string {
        return (string) ($this->fields[$key]['help'] ?? '');
    }

    /**
     * The minimum branch as a human readable release number, e.g. '5.1'.
     *
     * @return string
     */
    public function format_branch(): string {
        if ($this->requiresbranch === '') {
            return '';
        }

        // Branches are '405', '500', '501': major is everything but the last two digits.
        $major = substr($this->requiresbranch, 0, -2);
        $minor = (int) substr($this->requiresbranch, -2);

        return $major . '.' . $minor;
    }
}
