<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\symfony_mailer\Processor\EmailProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;

class Email implements InternalEmailInterface {

  use BaseEmailTrait;

  /**
   * The mailer.
   *
   * @var \Drupal\symfony_mailer\MailerInterface
   */
  protected $mailer;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;


  /**
   * @var string
   */
  protected $type;

  /**
   * @var string
   */
  protected $subType;

  /**
   * @var string
   */
  protected $entity_id;

  /**
   * @var string
   */
  protected $phase = 'preBuild';

  /**
   * @var \Drupal\symfony_mailer\Processor\EmailProcessorInterface[]
   */
  protected $body = [];

  /**
   * @var array
   */
  protected $processors = [];

  /**
   * @var string
   */
  protected $langcode;

  /**
   * @var string[]
   */
  protected $params = [];

  /**
   * @var string[]
   */
  protected $variables = [];

  /**
   * @var string
   */
  protected $theme = '';

  /**
   * @var array
   */
  protected $libraries = [];

  /**
   * The mail transport DSN.
   *
   * @var string
   */
  protected $transportDsn = '';

  /**
   * Constructs the Email object.
   *
   * @param \Drupal\symfony_mailer\MailerInterface $mailer
   *   Mailer service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param string $type
   *   Type. @see \Drupal\symfony_mailer\BaseEmailInterface::getType()
   * @param string $sub_type
   *   Sub-type. @see \Drupal\symfony_mailer\BaseEmailInterface::getSubType()
   * @param ?\Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   Entity. @see \Drupal\symfony_mailer\BaseEmailInterface::getEntity()
   */
  public function __construct(MailerInterface $mailer, RendererInterface $renderer, EntityTypeManagerInterface $entity_type_manager, ThemeManagerInterface $theme_manager, string $type, string $sub_type, ?ConfigEntityInterface $entity) {
    $this->mailer = $mailer;
    $this->renderer = $renderer;
    $this->entityTypeManager = $entity_type_manager;
    $this->themeManager = $theme_manager;
    $this->type = $type;
    $this->subType = $sub_type;
    $this->entity = $entity;
    $this->inner = new SymfonyEmail();
  }

  /**
   * Creates an email object.
   *
   * Use EmailFactory instead of calling this directly.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The current service container.
   * @param string $type
   *   Type. @see \Drupal\symfony_mailer\BaseEmailInterface::getType()
   * @param string $sub_type
   *   Sub-type. @see \Drupal\symfony_mailer\BaseEmailInterface::getSubType()
   * @param ?\Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   Entity. @see \Drupal\symfony_mailer\BaseEmailInterface::getEntity()
   *
   * @return static
   *   A new email object.
   */
  public static function create(ContainerInterface $container, string $type, string $sub_type, ?ConfigEntityInterface $entity = NULL) {
    return new static(
      $container->get('symfony_mailer'),
      $container->get('renderer'),
      $container->get('entity_type.manager'),
      $container->get('theme.manager'),
      $type,
      $sub_type,
      $entity
    );
  }

  /**
   * {@inheritdoc}
   */
  public function addProcessor(EmailProcessorInterface $processor) {
    $this->valid('preBuild');
    $this->processors[] = $processor;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLangcode(string $langcode) {
    $this->valid('preBuild');
    $this->langcode = $langcode;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode() {
    return $this->langcode;
  }

  /**
   * {@inheritdoc}
   */
  public function setParams(array $params = []) {
    $this->valid('preBuild');
    $this->params = $params;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setParam(string $key, $value) {
    $this->valid('preBuild');
    $this->params[$key] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getParams() {
    return $this->params;
  }

  /**
   * {@inheritdoc}
   */
  public function getParam(string $key) {
    return $this->params[$key] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function send() {
    $this->valid('preBuild');
    return $this->mailer->send($this);
  }

  /**
   * {@inheritdoc}
   */
  public function setBody($body) {
    $this->valid('preRender', 'preBuild');
    $this->body = $body;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function appendBody($body) {
    $this->valid('preRender', 'preBuild');
    $name = 'n' . count($this->body);
    $this->body[$name] = $body;
    return $this;
  }

  /**
   * {@inheritdoc}
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the entity.
   */
  public function appendBodyEntity(EntityInterface $entity, $view_mode = 'full') {
    $this->valid('preRender', 'preBuild');
    $build = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId())
      ->view($entity, $view_mode);

    $this->appendBody($build);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    $this->valid('preRender', 'preBuild');
    return $this->body;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariables(array $variables) {
    $this->valid('preRender', 'preBuild');
    $this->variables = $variables;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariable(string $key, $value) {
    $this->valid('preRender', 'preBuild');
    $this->variables[$key] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariables() {
    return $this->variables;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubType() {
    return $this->subType;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getSuggestions(string $initial, string $join) {
    $part_array = [$this->type, $this->subType];
    if (isset($this->entity)) {
      $part_array[] = $this->entity->id();
    }

    $part = $initial ?: array_shift($part_array);
    $suggestions[] = $part;

    while ($part_array) {
      $part .= $join . array_shift($part_array);
      $suggestions[] = $part;
    }

    return $suggestions;
  }

  /**
   * {@inheritdoc}
   */
  public function setTheme(string $theme_name) {
    $this->valid('preBuild');
    $this->theme = $theme_name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTheme() {
    if (!$this->theme) {
      $this->theme = $this->themeManager->getActiveTheme()->getName();
    }
    return $this->theme;
  }

  /**
   * {@inheritdoc}
   */
  public function addLibrary(string $library) {
    $this->libraries[] = $library;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return $this->libraries;
  }

  /**
   * {@inheritdoc}
   */
  public function setTransportDsn(string $dsn) {
    $this->transportDsn = $dsn;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTransportDsn() {
    return $this->transportDsn;
  }

  /**
   * {@inheritdoc}
   */
  public function process(string $function) {
    if ($function == 'preRender') {
      $this->valid('preBuild');
      $this->phase = 'preRender';
    }
    else {
      $this->valid($function);
    }

    usort($this->processors, function ($a, $b) use ($function) {
      return $a->getWeight($function) <=> $b->getWeight($function);
    });

    foreach ($this->processors as $processor) {
      call_user_func([$processor, $function], $this);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $this->valid('preRender');

    // Render subject.
    if ($this->subject instanceof MarkupInterface) {
      $this->subject = PlainTextOutput::renderFromHtml($this->subject);
    }

    // Render body.
    $body = ['#theme' => 'email', '#email' => $this];
    $html = $this->renderer->renderPlain($body);
    $this->phase = 'postRender';
    $this->setHtmlBody($html);
    $this->body = [];

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyEmail() {
    $this->inner->subject($this->subject);
    // No further alterations allowed.
    $this->phase = 'postSend';
    return $this->inner;
  }

  /**
   * Checks that a function was called in the correct phase.
   *
   * @param string $phase
   *   The correct phase.
   * @param string $alt_phase
   *   An alternative allowed phase.
   *
   * @return $this
   */
  protected function valid(string $phase, string $alt_phase = '') {
    $valid = ($this->phase == $phase) || ($this->phase == $alt_phase);
    if (!$valid) {
      $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
      throw new \LogicException("$caller function is only valid in the $phase phase");
    }
    return $this;
  }

}
