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

## Author

Marcus Green
