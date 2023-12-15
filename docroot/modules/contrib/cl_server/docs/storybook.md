# Add Storybook to your Drupal Site

There are two parts you need to configure to set up Storybook in Drupal. This is a one time setup for your Drupal site. After this, all the Storybook documentation will apply to your project. You can add addons, write stories, etc. provided they are compatible with Storybook's server framework.

## The Drupal Part

If you don't have a Drupal site install one from scratch. Install Drupal 10.1 or
later to use SDC provided by Drupal core.

If you haven't already, change the minimum stability to `dev` inside `composer.json`. This is so we can install the Drupal modules that don't have, yet, a stable release.

Next we need to install the Drupal module.

```console
composer require "drupal/cl_server:^2.0.0@beta"
drush pm:enable --yes cl_server
```

Next we need to enable and update `development.services.yml`. This tutorial assumes you start your site from scratch. If you already have enabled `settings.local.php` and development services, you can skip this step.

```console
# And enable local settings. Instructions are at the bottom of the file.
vim web/sites/default/settings.php

cp web/sites/example.settings.local.php web/sites/default/settings.local.php
vim web/sites/default/settings.local.php
# Disable caches during development. This allows finding new components without clearing caches.
$settings['cache']['bins']['discovery'] = 'cache.backend.null';
# Then disallow exporting config for 'cl_server'. Instructions are at the bottom of the file.
$settings['config_exclude_modules'] = ['devel', 'stage_file_proxy', 'cl_server'];

# And add Twig and Cors settings below.
vim web/sites/development.services.yml
```

In `development.services.yml` you want to add some configuration for Twig, so you don't need to clear caches so often. This is not needed for the Storybook integration, but it will make things easier when you need to move components to your Drupal templates.

You also need to enable CORS, so the Storybook application can talk to your Drupal site. You want this CORS configuration to be in `development.services.yml` so it does not get changed in your production environment. Remember _CL Server_ **SHOULD** be disabled in production.

The configuration you want looks like this:

```yaml
parameters:
  # ...
  twig.config:
    debug: true
    cache: false
  cors.config:
    enabled: true
    allowedHeaders: ['*']
    allowedMethods: []
    allowedOrigins: ['*']
    exposedHeaders: false
    maxAge: false
    supportsCredentials: true
services:
  # ...
```

Here you clear caches to have the dependency container pick up on the new config.

```console
drush cache:rebuild
```

### Prepare ddev for running the Storybook application
If you are using ddev for you local environment you will need to expose some ports to connect to Storybook. You can do so by adapting the following snippet in your `.ddev/config.yaml`:

```yaml
###############################################################################
# Customizations
###############################################################################
nodejs_version: "18"
webimage_extra_packages:
  - pkg-config
  - libpixman-1-dev
  - libcairo2-dev
  - libpango1.0-dev
  - make
web_extra_exposed_ports:
  - name: storybook
    container_port: 6006
    http_port: 6007
    https_port: 6006
web_extra_daemons:
  - name: node.js
    command: "tail -F package.json > /dev/null"
    directory: /var/www/html
hooks:
  post-start:
    - exec: echo '================================================================================='
    - exec: echo '                                  NOTICE'
    - exec: echo '================================================================================='
    - exec: echo 'The node.js container is ready. You can start storybook by typing:'
    - exec: echo 'ddev yarn storybook'
    - exec: echo
    - exec: echo 'By default it will be available at https://change-me.ddev.site:6006'
    - exec: echo "Use ddev describe to confirm if this doesn't work."
    - exec: echo 'Check the status of startup by running "ddev logs --follow --time"'
    - exec: echo '================================================================================='

###############################################################################
# End of customizations
###############################################################################
```

## The Storybook Part

Now it's time to install Storybook. You'll need `yarn`. Docs are at: https://storybook.js.org/addons/@lullabot/storybook-drupal-addon

Before you can install anything you'll need to do initialize the `package.json`. Then you can install the Drupal addon.

```console
# If you are using ddev, you'll need to prefix all yarn commands as usual.

# Update Yarn
yarn set version berry
# Skip pnp for now.
echo 'nodeLinker: node-modules' >> .yarnrc.yml
# Initialize the empty node.js project.
yarn init
```

### 🌴 Add Storybook to your Drupal repo
From the root of your repo:

```console
yarn global add sb@latest;
sb init --builder webpack5 --type server
# If you have a reason to use Webpack4 use the following instead:
# sb init --type server
yarn add -D @lullabot/storybook-drupal-addon
```

#### 🤔 If that didn't work...
At the time of writing this Storybook 7 contains a bug that will prevent server rendered components from being discovered. If you encounter this, start over and try this installation method.

To start over:
```console
# This will delete a bunch of stuff. If this is a fresh install, this is safe.
rm --recursive --force node_modules yarn.lock .yarn .yarnrc.yml package.json stories
```

Now install the packages manually:
```console
yarn add --dev @lullabot/storybook-drupal-addon@^1.0.27 \
  @babel/core@^7.21.4 \
  @mdx-js/react@^1.6.22 \
  @storybook/addon-actions@^6.5.16 \
  @storybook/addon-docs@^6.5.16 \
  @storybook/addon-essentials@^6.5.16 \
  @storybook/addon-links@^6.5.16 \
  @storybook/builder-webpack5@^6.5.16 \
  @storybook/cli@^6.5.16 \
  @storybook/manager-webpack5@^6.5.16 \
  @storybook/server@^6.5.16 \
  babel-loader@^8.3.0
```

Now add the following scripts to your `package.json`:

```console
  "scripts": {
    "storybook": "start-storybook -p 6006",
    "build-storybook": "build-storybook"
  }
```

<details><summary>Storybook configuration files</summary>

```javascript
// .storybook/main.js
module.exports = {
  stories: [
    '../web/themes/**/*.stories.mdx',
    '../web/themes/**/*.stories.@(json|yml)',
    '../web/modules/**/*.stories.mdx',
    '../web/modules/**/*.stories.@(json|yml)',
  ],
  addons: [
    '@storybook/addon-links',
    '@storybook/addon-essentials',
    '@lullabot/storybook-drupal-addon'
  ],
  framework: '@storybook/server',
  core: {
    builder: '@storybook/builder-webpack5'
  }
}
```

```javascript
// .storybook/preview.js
export const parameters = {
  server: {
    // Replace this with your Drupal site URL, or an environment variable.
    url: 'https://change-me.ddev.site',
  },
  globals: {
    drupalTheme: 'umami',
    supportedDrupalThemes: {
      umami: {title: 'Umami'},
      claro: {title: 'Claro'},
    },
  }
};
```
</details>

### 🌵 Configure Storybook
First enable the addon. Add it to the `addons` in the `.storybook/main.js`. Also
remember to point to where your stories are.

Take for example this `.storybook/main.js`.

```javascript
// .storybook/main.js
module.exports = {
  // Change the place where storybook searched for stories.
  stories: [
    "../web/themes/**/*.stories.mdx",
    "../web/themes/**/*.stories.@(json|yml)",
    "../web/modules/**/*.stories.mdx",
    "../web/modules/**/*.stories.@(json|yml)",
  ],
  // ...
  addons: [
    '@storybook/addon-links',
    '@storybook/addon-essentials',
    '@lullabot/storybook-drupal-addon', // <----
  ],
  framework: '@storybook/server',
  core: {
    builder: '@storybook/builder-webpack5'
  }
};
```

Then, configure the `supportedDrupalThemes` and `drupalTheme` parameters in `.storybook/preview.js`.

`supportedDrupalThemes` is an object where the keys are the machine name of the Drupal themes and the values are the plain text name of that Drupal theme you want to use. This is what will appear in the dropdown in the toolbar.

```javascript
// .storybook/preview.js
export const parameters = {
  // ...
  server: {
    // Replace this with your Drupal site URL, or an environment variable.
    url: 'http://local.contrib.com',
  },
  globals: {
    drupalTheme: 'olivero',
    supportedDrupalThemes: {
      olivero: {title: 'Olivero'},
      claro: {title: 'Claro'},
    },
  }
  // ...
};
```

## Start Storybook

Start the Storybook's development server:

```console
yarn storybook
# ddev yarn storybook
```

If you are using ddev, use https://change-me.ddev.site:6006 as per the configuration above. Change `change-me` with your actual ddev project name.

Storybook will start in http://localhost:6006 and you will see a black and red screen with an error. This is because you need to set the Components config in Drupal.

If you want to see the Example components to see if things are working, then you need to install the [_SDC Examples_](https://www.drupal.org/project/sdc_examples) module. This project also contains examples of Storybook stories in YAML format for your components.

You'll refresh and you will see another error. This is a 403 because you need to allow the CL Server render endpoint for the anonymous user. So you need to go to the Drupal permissions page and grant permission to the anonymous user to access the component rendering endpoint. Note that this permission will not be exported into configuration because you excluded `cl_server` above in `settings.local.php`.

Now you can restart the Storybook server. Kill the `yarn storybook` process and run it again.

## Nested component example

This example shows how to create a nested component using `slots`, and use it inside Storybook as well.

### Create the component in sdc

```
# bdc-grid.component.yml

'$schema': 'https://git.drupalcode.org/project/drupal/-/raw/10.1.x/core/modules/sdc/src/metadata.schema.json'
name: "Bdc Grid"
status: "stable"
slots:
  grid_items:
    title: Grid items
props:
  type: object
  properties:
    cols:
      type: string
      title: Columns
      description: Grid columns
      examples:
        - "col-12 col-md-6 col-lg-4"
```

```
# bdc-grid.twig

<section {{ attributes.addClass('bdc-grid') }}>
  <div class="container">
    <div class="row">
      {% block grid_items %}{% endblock grid_items %}
    </div>
  </div>
</section>
```

### Use the component in Drupal template
In this case, I'm looping through a field reference (content) and rendering the items inside the grid.
```
# node--page--full.html.twig

{% embed 'my_new_theme:bdc-grid' with {'cols': 'col-md-4'} %}
  {% block grid_items %}
    {% for key, item in content.field_article_reference %}
      {%  if key|first != '#' %}
        <div class="{{ cols }}">
          {{ item }}
        </div>
      {% endif %}
    {% endfor %}
  {% endblock grid_items %}
{% endembed %}
```
### Use the component in Storybook

```
# bdc-grid.stories.yml

title: Layout/Grid
stories:
  - name: Grid
    args:
      grid_items: |
        <div class="col-12 col-md-6 col-lg-4">{% include 'my_new_theme:bdc-button' with { bdc_button_title: 'Drupal button'} %}</div>
        <div class="col-12 col-md-6 col-lg-4">{% include 'my_new_theme:bdc-button' with { bdc_button_title: 'Drupal button'} %}</div>
        <div class="col-12 col-md-6 col-lg-4">{% include 'my_new_theme:bdc-button' with { bdc_button_title: 'Drupal button'} %}</div>
```

