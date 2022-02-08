<?php

namespace Drupal\addtocal\Form;

use Drupal\addtocal\AddToCalTypeManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AddToCalForm
 */
class AddToCalForm extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * @var array
   */
  protected $settings;

  /**
   * @var int
   */
  protected $delta;

  /**
   * AddToCalForm constructor.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param $settings
   * @param $delta
   */
  public function __construct(EntityInterface $entity, $settings, $delta) {
    $this->entity = $entity;
    $this->settings = $settings;
    $this->delta = $delta;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    $form_id = 'addtocal_' . $this->entity->bundle() . '_' . $this->settings['field_name'] . '_' . $this->entity->id();
    return $form_id;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param null $entity
   * @param null $settings
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Need both entity and settings to do anything of note
    if (!is_object($this->entity) || empty($this->settings)) {
      return $form;
    }

    // Always open in new tab
    $form['#attributes']['target'] = '_blank';

    // CSS / JS Libraries
    $form['#attributes']['class'][] = 'addtocal-form';
    $form['#attached']['library'][] = 'addtocal/addtocal';

    // Pass through to submit
    $form['entity'] = [
      '#type' => 'value',
      '#value' => $this->entity,
    ];
    $form['settings'] = [
      '#type' => 'value',
      '#value' => $this->settings,
    ];
    $form['delta'] = [
      '#type' => 'value',
      '#value' => $this->delta,
    ];

    // Unique element based on field
    $entity_type = $this->entity->bundle();
    $field_name = $this->settings['field_name'];
    $element_id = 'addtocal-' . $entity_type . '-' . $field_name . '-' . $this->entity->id() . '--' . $this->delta;

    // Wrap the form output for easier styling
    $form['addtocal_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['addtocal-container'],
      ],
    ];

    $form['addtocal_container']['addtocal_btn'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['addtocal'],
      ],
      '#id' => $element_id,
      '#markup' => $this->t('Add to Calendar'), // @TODO: make this part of field config
    ];

    // @TODO: make selection options part of the field config
    $form['addtocal_container']['type'] = [
      '#type' => 'radios',
      '#options' => [
        'generic' => $this->t('iCal / Outlook'),
        'google' => $this->t('Google'),
        'yahoo' => $this->t('Yahoo!'),
      ],
      '#attributes' => [
        'class' => ['addtocal-menu'],
        'onclick' => 'this.form.submit();'
      ],
      '#id' => $element_id . '-menu',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'class' => ['addtocal-submit'],
      ],
    ];

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $entity = $values['entity'];
    $settings = $values['settings'];
    $delta = $values['delta'];
    $type = $values['type'];

    /** @var AddToCalTypeManager $manager */
    $manager = \Drupal::service('plugin.manager.addtocal.type');
    $instance = $manager->createInstance($type);

    // Process the plugin submit
    $instance->downloadSubmit($entity, $settings, $delta, $form_state);
  }

}
