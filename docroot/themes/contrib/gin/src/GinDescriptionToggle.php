<?php

namespace Drupal\gin;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service to handle toggling form descriptions.
 */
class GinDescriptionToggle implements ContainerInjectionInterface {


  /**
   * The content form helper class.
   *
   * @var \Drupal\gin\GinContentFormHelper
   */
  protected $contentFormHelper;

  /**
   * The gin theme settings class.
   *
   * @var \Drupal\gin\GinSettings
   */
  protected $ginSettings;

  /**
   * GinDescriptionToggle constructor.
   *
   * @param \Drupal\gin\GinContentFormHelper $contentFormHelper
   *   The content form helper class.
   */
  public function __construct(GinContentFormHelper $contentFormHelper) {
    $this->ginSettings = NULL;

    /** @see https://www.drupal.org/project/gin/issues/3325263 */
    if (!empty(\Drupal::hasService('user.data'))) {
      $this->ginSettings = \Drupal::service('class_resolver')->getInstanceFromDefinition(GinSettings::class);
    }

    $this->contentFormHelper = $contentFormHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $classResolver = $container->get('class_resolver');

    return new static(
      $classResolver->getInstanceFromDefinition(GinContentFormHelper::class)
    );
  }

  /**
   * Generic preprocess enabling toggle.
   *
   * @param array $variables
   *   The variables array (modify in place).
   */
  public function preprocess(array &$variables) {
    if ($this->isEnabled()) {
      if (!empty($variables['description'])) {
        $variables['description_display'] = 'invisible';
        $variables['description_toggle'] = TRUE;
      }
      // Add toggle for text_format, description is in wrapper.
      elseif (!empty($variables['element']['#description_toggle'])) {
        $variables['description_toggle'] = TRUE;
      }
    }
  }

  /**
   * Functionality is enabled via setting on content forms.
   *
   * @return bool
   *   Wether feature is enabled or not.
   */
  public function isEnabled() {

    if (empty($this->ginSettings)) {
      return FALSE;
    }

    return $this->ginSettings->get('show_description_toggle') && $this->contentFormHelper->isContentForm();
  }

}
