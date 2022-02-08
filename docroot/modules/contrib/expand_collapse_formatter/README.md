#SUMMARY
This is a simple module to expand or collapse long texts. It provides a
configurable formatter which can be attached to each text area field of an
entity.

#REQUIREMENTS
This module only works on certain field types:
* "text_long"
* "string_long"
* "text_with_summary"

#DETAILED STEPS
1. After enabling this module, navigate to your entity type's "manage display"
tab.
2. Choose the "Expand collapse formatter"
3. Adjust the settings using the gear icon. You can change the "trim length",
"default state", open and close text, and the classes used for each action.
Click "Update" to save these settings and then remember to click "Save" at the
bottom of the page.

After these steps, you should see the "read more" link at the bottom of the
fields with this formatter, so long as they contain more characters than the
trim length.
