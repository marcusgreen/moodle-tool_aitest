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
 * Turns a preset plus the admin's answers into an AI provider instance.
 *
 * Provider instances arrived in Moodle 5.0 (MDL-82977). On 4.5 a provider is a plugin
 * configured once, so installing a preset there could only overwrite the site's single
 * configuration for that provider, with nothing to install it alongside and no way back.
 * Rather than do that, {@see self::is_supported()} gates the whole feature and 4.5 is
 * told why.
 *
 * @package    tool_aitest
 * @copyright  2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class installer {
    /**
     * Does this Moodle store AI providers as instances we can add one to?
     *
     * Detected from the API rather than from $CFG->release, so a branch this code has
     * never heard of is judged on what it actually offers.
     *
     * @return bool
     */
    public static function is_supported(): bool {
        global $DB;

        return class_exists(\core_ai\manager::class)
            && method_exists(\core_ai\manager::class, 'create_provider_instance')
            && $DB->get_manager()->table_exists('ai_providers');
    }

    /**
     * Create a provider instance from a preset.
     *
     * @param preset $preset The preset to install.
     * @param string $name Instance name, as confirmed by the admin.
     * @param array $values Config values from the install form, keyed as the preset's config is.
     * @param bool $enabled Whether to enable the new instance.
     * @param array $actionvalues Action settings the admin supplied: action class => [key => value].
     * @return \core_ai\provider The created instance.
     * @throws \moodle_exception If the site cannot take provider instances, or the preset is not usable here.
     */
    public static function install(
        preset $preset,
        string $name,
        array $values,
        bool $enabled,
        array $actionvalues = [],
    ): \core_ai\provider {
        if (!self::is_supported()) {
            throw new \moodle_exception('presetneeds50', 'tool_aitest');
        }
        if (!$preset->provider_installed()) {
            throw new \moodle_exception('presetprovidermissing', 'tool_aitest', '', $preset->plugin);
        }

        return \core\di::get(\core_ai\manager::class)->create_provider_instance(
            classname: $preset->get_provider_class(),
            name: self::unique_name($name !== '' ? $name : $preset->name),
            enabled: $enabled,
            config: self::build_config($preset, $values),
            actionconfig: self::build_action_config($preset, $actionvalues),
        );
    }

    /**
     * Merge the preset's config with what the admin typed.
     *
     * Values are cleaned by what the provider's own form declares the field to be, so a
     * preset cannot write arbitrary content into ai_providers.config.
     *
     * @param preset $preset The preset being installed.
     * @param array $values Config values from the install form.
     * @return array The instance config.
     */
    private static function build_config(preset $preset, array $values): array {
        $config = [];
        foreach ($preset->config as $key => $default) {
            $value = array_key_exists($key, $values) ? $values[$key] : $default;
            if ($value === null || $value === '') {
                // Core's own action form filters false-y values out before storing;
                // an empty setting and an absent one mean the same thing here too.
                continue;
            }
            $config[$key] = clean_param((string) $value, self::param_type($key));
        }

        return $config;
    }

    /**
     * Build the full action config for the new instance.
     *
     * Core expects every action the provider supports to be present, so start from the
     * provider's own defaults and lay the preset's settings over the top. That way a
     * preset that only describes generate_text still produces a valid instance.
     *
     * @param preset $preset The preset being installed.
     * @param array $actionvalues Action settings the admin supplied: action class => [key => value].
     * @return array The action config, keyed by action class name.
     */
    private static function build_action_config(preset $preset, array $actionvalues = []): array {
        $providerclass = $preset->get_provider_class();
        $actionconfig = $providerclass::initialise_action_settings();

        foreach ($preset->actions as $actionclass => $action) {
            $actionclass = ltrim((string) $actionclass, '\\');
            if (!array_key_exists($actionclass, $actionconfig)) {
                // The preset names an action this provider does not support, or one
                // this Moodle does not have (explain_text did not exist before 5.0).
                // Skipping is right: the rest of the preset is still good.
                continue;
            }

            $settings = is_array($action['settings'] ?? null) ? $action['settings'] : [];
            $settings = array_merge($settings, $actionvalues[$actionclass] ?? []);
            foreach ($settings as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                if (is_array($value)) {
                    // modelextraparams is stored as a JSON string, but writing escaped
                    // JSON inside JSON by hand is exactly what this feature should spare
                    // people, so accept an object and encode it.
                    $value = json_encode($value);
                }
                $settings[$key] = clean_param((string) $value, self::param_type($key));
            }

            $actionconfig[$actionclass]['settings'] = array_merge(
                $actionconfig[$actionclass]['settings'] ?? [],
                $settings,
            );
            if (isset($action['enabled'])) {
                $actionconfig[$actionclass]['enabled'] = (bool) $action['enabled'];
            }
        }

        return $actionconfig;
    }

    /**
     * The cleaning type for a config or setting key.
     *
     * Mirrors the setType() calls in the provider plugins' own forms.
     *
     * @param string $key The key being written.
     * @return string A PARAM_* constant.
     */
    private static function param_type(string $key): string {
        return match ($key) {
            'endpoint', 'apiendpoint', 'docsurl' => PARAM_URL,
            'deployment', 'apiversion' => PARAM_ALPHANUMEXT,
            'enablebasicauth', 'enableglobalratelimit', 'enableuserratelimit' => PARAM_INT,
            'globalratelimit', 'userratelimit' => PARAM_INT,
            default => PARAM_TEXT,
        };
    }

    /**
     * A provider instance name not already in use.
     *
     * Colliding with an existing instance is not worth failing an install over, and
     * silently creating a second "OpenAI" is worse than creating "OpenAI (2)".
     *
     * @param string $name The wanted name.
     * @return string The name to use.
     */
    private static function unique_name(string $name): string {
        global $DB;

        $existing = $DB->get_fieldset_select('ai_providers', 'name', '');
        if (!in_array($name, $existing, true)) {
            return $name;
        }

        $suffix = 2;
        while (in_array("{$name} ({$suffix})", $existing, true)) {
            $suffix++;
        }

        return "{$name} ({$suffix})";
    }
}
