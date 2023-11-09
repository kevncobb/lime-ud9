## Libraries UI

This module will provide a UI to display all libraries provide by modules and themes.
Once libraries_ui has been installed visit _/admin/reports/libraries_ to get all
libraries' information.

For site builder, this is perfect to find out about your libraries
in your website. For developers, this module contains a very light-weight method that
allows you to get all the information about libraries in an array. This can be very
helpful if you need to use the libraries data for other modules.

### Install
Installing via composer is recommended.

```bash
composer require drupal/libraries_ui
```

Install as you would normally install a contributed Drupal module. See:
https://www.drupal.org/documentation/install/modules-themes/modules-8
for further information.

### Drush commands

Drush command is provided to help with debugging or listing out libraries.

```bash
drush libraries:debug
```

### Maintainers

* George Anderson (geoanders) - https://www.drupal.org/u/geoanders
* Darryl Norris (darol100) - https://www.drupal.org/u/darol100
