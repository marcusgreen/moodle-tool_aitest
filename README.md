### Moodle 4.5 AI Tester

The Moodle AI Subsystem does not have a diagnostic mode to indicate if there is an issue with connections to the remote LLM.


This plugin is designed to help diagnose issues with the AI Subsystem.
It makes a call to the generate_text function and will return either
Confirmed! if the connection has worked or a message indicating the nature of the problem if the connection has not worked.

Install and run

Place the code in the folder /admin/tool/aitest
Run the upgrade/install
With admin rights view the /admin/tool/aitest page


Marcus Green

