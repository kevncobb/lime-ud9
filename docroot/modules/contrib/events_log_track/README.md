# Event logs Track

## Introduction
This module track logs of specific events that you'd like to log. The events  by
the user (using the forms) are saved in the database and can be viewed on the
page admin/reports/events-track. You could use this to track number of times the
CUD operation performed by which users.

Added Event Log Track UI to see the logs directly.

Currently, the following sub modules of Events Log Track are supported:
- User authentication (login/logout/request password)
- User authentication via TFA
- Cache clear
- Comment (CUD operations)
- Configuration changes
- File (CUD operations)
- Group (CUD operations)
- Media (CUD operations)
- Menu (custom menu's and menu items CUD operations)
- Node (CUD operations)
- Taxonomy (vocabulary and term CUD operations)
- User (CUD operations)
- Workflows (CUD operations)

The event log track can be easily extended with custom events.

## Installation
Enable the module and the sub modules for the events that you'd like to log.
From that point onwards the events will be logged.

After performing some operations the page admin/reports/events-track will show
the events.

### Support for syslog
- Enable event_log_track_syslog.
- Configure message in syslog settings.
- Go to event_log_track.settings_form (/admin/config/system/events-log-track).
- Disable logging to DB.
- Recommend uninstalling event_log_track_ui as it's not needed.

### Clearing logs
- Go to event_log_track.settings_form (/admin/config/system/events-log-track).
- Enable Enable log deletion.
- Configure how long to keep records.

## Maintainers
- Stephen Mustgrave - [smustgrave](https://www.drupal.org/u/smustgrave)
- Atulesh Kumar - [kumaratulesh](https://www.drupal.org/u/kumaratulesh)
