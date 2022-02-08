<?php
/**
 * @file
 * Add custom theme settings to the ZURB Foundation sub-theme.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Implements hook_form_FORM_ID_alter().
 * @param $form
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 */
function limestone_form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {

  // Email logo settings to be used with Varbase Email module.
  $form['email_logo'] = [
    '#type'     => 'details',
    '#title'    => t('Email Logo'),
    '#open' => false,
  ];
  
  $form['email_logo']['email_logo_default'] = [
    "#type" => "checkbox",
    '#title'    => t('Use the logo supplied by the theme'),
    "#default_value" => theme_get_setting('email_logo_default'),
  ];

  $form['email_logo']['email_logo_settings'] = [
    "#type" => "container",
    '#states' => [
      "invisible" => [
        'input[name="email_logo_default"]' => [
          "checked" => TRUE,
        ]
      ]
    ]
  ];

  $form['email_logo']['email_logo_settings']["email_logo_path"] = [
    "#type" => "textfield",
    "#title" => "Path to custom logo",
    "#default_value" => theme_get_setting('email_logo_path'),
    "#description" => t("Examples: <code>@external-file</code>", ["@external-file"=> "http://www.example.com/logo.png"])
  ];

  $form['email_logo']['email_logo_settings']["email_logo_upload"] = [
    '#type'     => 'managed_file',
    "#title"    => t("Upload logo image"),
    "#description" => t("If you don't have direct file access to the server, use this field to upload your logo."),
    '#required' => FALSE,
    '#upload_location' => file_default_scheme() . '://theme/email_logo/',
    '#default_value' => theme_get_setting('email_logo_upload'),
    '#upload_validators' => [
      'file_validate_extensions' => ['gif png jpg jpeg'],
    ],
  ];
}
