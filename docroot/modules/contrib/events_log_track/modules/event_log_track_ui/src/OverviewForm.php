<?php

namespace Drupal\event_log_track_ui;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\event_log_track\EventLogStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Configure user settings for this site.
 */
class OverviewForm extends FormBase {

  /**
   * Variable for filters.
   *
   * @var array
   *  The form filters
   */
  private array $filters = [];

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $userStorage;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected Request $request;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a new OverviewForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $user_storage
   *   The custom block storage.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityStorageInterface $user_storage, Request $request, EntityTypeManagerInterface $entity_type_manager) {
    $this->userStorage = $user_storage;
    $this->request = $request;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $entity_type_manager->getStorage('user'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Return user link.
   */
  private function getUserData($uid) {
    if (empty($uid)) {
      return Markup::create('<em>' . $this->t('Anonymous') . '</em>');
    }

    $account = $this->userStorage->load($uid);
    if (empty($account)) {
      return Markup::create('<em>' . $this->t('@uid (deleted)', [
        '@uid' => $uid,
      ]) . '<em>');
    }

    return Link::fromTextAndUrl($account->getAccountName(), Url::fromUri('internal:/user/' . $account->id()));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_log_track_filter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['filters'] = [
      '#prefix' => '<div class="container-inline">',
      '#type' => 'details',
      '#title' => $this->t('Filter'),
      '#open' => TRUE,

    ];
    $form['#attached']['library'][] = 'event_log_track/log_filter_form';

    $handlers = event_log_track_ui_get_event_handlers();
    $options = [];
    foreach ($handlers as $type => $handler) {
      $options[$type] = $handler['title'];
    }
    $form['filters']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Type'),
      '#options' => ['' => $this->t('Select a type')] + $options,
      '#ajax' => [
        'callback' => '::formGetAjaxOperation',
        'event' => 'change',
      ],
    ];

    $form['filters']['operation'] = EventLogStorage::formGetOperations(empty($form_state->getValue('type')) ? '' : $form_state->getValue('type'));

    $form['filters']['user'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#selection_settings' => ['include_anonymous' => FALSE],
      '#title' => $this->t('User'),
      '#placeholder' => $this->t('User who triggered this event.'),
      '#size' => 30,
      '#maxlength' => 60,
    ];

    $form['filters']['id'] = [
      '#type' => 'textfield',
      '#size' => 30,
      '#title' => $this->t('ID'),
      '#placeholder' => $this->t('Id of the events (numeric).'),
    ];

    $form['filters']['ip'] = [
      '#type' => 'textfield',
      '#size' => 30,
      '#title' => $this->t('IP'),
      '#placeholder' => $this->t('IP address of the visitor.'),
    ];

    $form['filters']['name'] = [
      '#type' => 'textfield',
      '#size' => 30,
      '#title' => $this->t('Name'),
      '#placeholder' => $this->t('Name or machine name.'),
    ];

    $form['filters']['path'] = [
      '#type' => 'textfield',
      '#size' => 30,
      '#title' => $this->t('Path'),
      '#placeholder' => $this->t('keyword in the path.'),
    ];

    $form['filters']['keyword'] = [
      '#type' => 'textfield',
      '#size' => 30,
      '#title' => $this->t('Description'),
      '#placeholder' => $this->t('Keyword in the description.'),
      '#suffix' => '</div>',
    ];

    $form['filters']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];

    $header = [
        [
          'data' => $this->t('Updated'),
          'field' => 'created',
          'sort' => 'desc',
        ],
        ['data' => $this->t('Type'), 'field' => 'type'],
        ['data' => $this->t('Operation'), 'field' => 'operation'],
        ['data' => $this->t('Path'), 'field' => 'path'],
        ['data' => $this->t('Description'), 'field' => 'description'],
        ['data' => $this->t('User'), 'field' => 'uid'],
        ['data' => $this->t('IP'), 'field' => 'ip'],
        ['data' => $this->t('ID'), 'field' => 'ref_numeric'],
        ['data' => $this->t('Name'), 'field' => 'ref_char'],
    ];

    $this->getFiltersFromUrl($form);
    $result = EventLogStorage::getSearchData($this->filters, $header, 20);

    if (!empty($this->filters)) {
      $form['filters']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#limit_validation_errors' => [],
        '#submit' => ['::resetForm'],
      ];
    }

    $rows = [];
    foreach ($result as $record) {
      $userLink = $this->getUserData($record->uid);
      $rows[] = [
          ['data' => date("Y-m-d H:i:s", $record->created)],
          ['data' => $record->type],
          ['data' => $record->operation],
          ['data' => $record->path],
          ['data' => strip_tags(htmlspecialchars_decode($record->description))],
          ['data' => $userLink],
          ['data' => $record->ip],
          ['data' => $record->ref_numeric],
          ['data' => $record->ref_char],
      ];
    }

    // Generate the table.
    $build['config_table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No events found.'),
    ];

    // Finally, add the pager.
    $build['pager'] = [
      '#type' => 'pager',
      '#parameters' => $this->filters,
    ];

    $form['results'] = $build;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->disableRedirect();
    $form_state->setRebuild();
    $this->setFilters($form_state);
  }

  /**
   * Resets all the states of the form.
   *
   * This method is called when the "Reset" button is triggered. Clears
   * user inputs and the form state.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('<current>');
    $form_state->setValues([]);
    $this->filters = [];
  }

  /**
   * Retrieves form filters from the URL.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   */
  public function getFiltersFromUrl(array &$form) {
    $url_params = $this->request->query->all();
    if (!empty($url_params)) {
      unset($url_params['page']);
      $this->filters = $url_params;
      foreach ($this->filters as $field => $value) {
        if (isset($form['filters'][$field])) {
          if ($field === 'user') {
            $form['filters'][$field]['#default_value'] = $this->userStorage->load($value);
          }
          else {
            $form['filters'][$field]['#default_value'] = $value;
          }
        }
      }
    }
  }

  /**
   * Stores form filters in the URL.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function setFilters(FormStateInterface $form_state) {
    $this->filters = [];
    $values = $form_state->getValues();
    foreach ($values as $field => $value) {
      if ($field === 'submit') {
        break;
      }
      elseif (isset($value) && $value !== "") {
        $this->filters[$field] = $value;
      }
    }
    $this->request->query->replace($this->filters);
  }

  /**
   * Ajax callback for the operations options.
   */
  public function formGetAjaxOperation(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();

    $element = EventLogStorage::formGetOperations($form_state->getValue('type'));
    $ajax_response->addCommand(new HtmlCommand('#operation-dropdown-replace', $element));

    return $ajax_response;
  }

}
