SendGrid API

https://www.drupal.org/project/sendgrid_api

This module is intended for developer purposes only. It does not provide any end
user features on its own. You will only need this module if another module
requests it.

# Requirements

This project uses the SendGrid packagist library. You should install the module
with Composer so your dependencies are managed automatically. Support is not
provided for non-Composer installations.

# Installation

Install the module as you would with any other Drupal project.

Enabling the module will force the Key project to be installed if it is not
already.

# Configuration

 1. Add your API at /admin/config/system/keys/add.
 2. Under *Key type*, select _SendGrid_ option.
 3. Enter your 69 character SendGrid key in the "Key value" field.
 4. Save the form.
 5. Configure SendGrid to use your key at /admin/config/services/sendgrid.
 6. Select the previously created SendGrid key under *API key*.
 7. Save the form.

# Usage

Examples of usage:

```php
/* @var \SendGrid $sendGrid */
$sendGrid = \Drupal::service('sendgrid_api.client');

// Get all marketing lists:
try {
  $response = $sendGrid->client->marketing()->lists()->get();
  $json = $response->body();
  $decoded = \Drupal\Component\Serialization\Json::decode($json);

  $listIds = array_map(function (array $result): string {
     return $result['id'];
  }, $decoded['result']);
}
catch (\Exception $e) {
}
```

The Sendgrid library also provides various examples in its 
[/examples/][examples] directory.

[examples]: https://github.com/sendgrid/sendgrid-php/tree/master/examples

# License

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
