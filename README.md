# Moodle AI Tester (tool_aitest)

A Moodle admin tool that provides a diagnostic mode for the Moodle AI Subsystem, which otherwise gives no easy way to tell whether connections to a remote LLM are working.

## What it does

The plugin makes a call to the AI Subsystem's `generate_text` function and reports the result:

- **Confirmed!** — the connection to the configured LLM provider succeeded.
- A descriptive error message — the connection failed, with an indication of the cause (for example a blocked endpoint, missing provider configuration, or an authentication problem). Where relevant the message links to the settings page needed to fix it.

## Requirements

- Moodle 5.0 or later (a configured AI provider in the AI Subsystem).
- Administrator access.

## Installation

1. Place the code in `admin/tool/aitest`.
2. Log in as an administrator and run the upgrade/install to complete installation.
3. Visit `/admin/tool/aitest` to run the diagnostic.

## Testing from the provider settings form

On the core *Generate text* action settings page (Site admin > AI > AI providers > settings for an action) the plugin adds a **Save and run AI test** button. It saves the settings currently in the form, then runs the test against them and reports the result at the top of the same settings page, so you can adjust and retest without leaving the form. A link on the result opens the full test page with the raw response detail.

## Provider presets

Site admin > Tools > AI provider presets (also linked from the plugin's settings page).

A preset is a JSON file carrying everything needed to configure an AI provider except
the parts that belong to your account: the API key, and anything site-specific such as
an Azure deployment name or a Bedrock model id. Pick one, supply those, and the provider
instance is created and immediately tested against the prompt this plugin already uses,
so you find out whether it works before you rely on it.

Presets are created disabled by default. The test runs either way, which means a wrong
key cannot quietly take over live AI traffic through the provider ordering while you find
out.

Presets ship for the providers that Moodle bundles:

| Preset | Provider plugin | Needs |
|---|---|---|
| OpenAI GPT-4o | `aiprovider_openai` | Moodle 4.5+ |
| Azure AI | `aiprovider_azureai` | Moodle 4.5+ |
| Ollama (local) | `aiprovider_ollama` | Moodle 5.0+ |
| DeepSeek | `aiprovider_deepseek` | Moodle 5.1+ |
| Google Gemini | `aiprovider_gemini` | Moodle 5.2+ |
| AWS Bedrock | `aiprovider_awsbedrock` | Moodle 5.2+ |

Presets whose provider plugin this site does not have are listed with the reason rather
than hidden, so the page matches the documentation whatever version you are on.

**Installing presets requires Moodle 5.0 or later.** 5.0 was the first version to store
AI providers as instances that can sit side by side; on 4.5 a provider is a plugin
configured once, so installing a preset could only overwrite the site's single
configuration for that provider, with nothing to compare against and no way back. The
test and diagnostic pages work on 4.5 exactly as before.

## Author

Marcus Green
