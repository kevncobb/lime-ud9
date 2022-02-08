<?php

namespace Drupal\addtocal\Plugin\AddToCal\Type;

use Drupal\addtocal\AddToCalTypeBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Provides generic calendar type (ics).
 *
 * @AddToCalType(
 *   id = "generic",
 *   label = @Translation("Generic Calendar"),
 *   description = @Translation("Generic calendar type (ics).")
 * )
 */
class Generic extends AddToCalTypeBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * File handle of the current ICS stream.
   *
   * @var resource
   */
  private $_fd;

  /**
   * Add in the filesystem service for writing the ICS
   *
   * {@inheritdoc}
   */
  function __construct(array $configuration, $plugin_id, $plugin_definition, $dateFormatter, FileSystemInterface $file_system) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $dateFormatter);
    $this->fileSystem = $file_system;
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter'),
      $container->get('file_system')
    );
  }

  /**
   * Open the stream.
   */
  public function open($uri) {
    // Open in write mode. Will overwrite the stream if it already exists.
    $this->_fd = fopen($uri, 'w');
  }

  /**
   * Implements Drupal\Component\Gettext\PoStreamInterface::close().
   *
   * @throws Exception
   *   If the stream is not open.
   */
  public function close() {
    if ($this->_fd) {
      fclose($this->_fd);
    }
    else {
      throw new Exception('Cannot close stream that is not open.');
    }
  }

  /**
   * Write data to the stream.
   *
   * @param string $data
   *   Piece of string to write to the stream. If the value is not directly a
   *   string, casting will happen in writing.
   *
   * @throws Exception
   *   If writing the data is not possible.
   */
  private function write($data) {
    $result = fputs($this->_fd, $data);
    if ($result === FALSE) {
      throw new Exception('Unable to write data: ' . substr($data, 0, 20));
    }
  }


  /**
   * Generates an ICS file for download
   */
  public function generateStructure(array $info) {

    $uri = $this->fileSystem->tempnam('temporary://', 'ics_');

    $title = Html::escape($info['title']);

    $ics = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:{$info['entity_type']}-{$info['entity_id']}@{$_SERVER['HTTP_HOST']}
DTSTAMP:{$info['rfc3339']['start']}
DTSTART:{$info['rfc3339']['start']}
DTEND:{$info['rfc3339']['end']}
SUMMARY:{$title}
DESCRIPTION:{$info['description']}
LOCATION:{$info['location']}
END:VEVENT
END:VCALENDAR
ICS;

    $this->open($uri);
    $this->write($ics);
    $this->close();

    return $uri;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param array $settings
   * @param int $delta
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function downloadSubmit(EntityInterface $entity, array $settings, $delta, FormStateInterface $form_state) {
    $eventDetails = $this->extractEventDetails($entity, $settings, $delta);

    $filename = preg_replace('/[\x00-\x1F]/u', '_', strip_tags($eventDetails['title'])) . '.ics';

    $ics = $this->generateStructure($eventDetails);

    $response = new BinaryFileResponse($ics);
    $response->headers->set('Content-Type', 'application/calendar; charset=utf-8');
    $response->setContentDisposition('attachment', $filename);

    $form_state->setResponse($response);
  }

}
