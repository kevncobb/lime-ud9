<?php

namespace Drupal\pdf_api\Plugin\PdfGenerator;

use Dompdf\Dompdf;
use Dompdf\Options;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\pdf_api\Plugin\PdfGeneratorBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

// Disable DOMPDF's internal autoloader if you are using Composer.
define('DOMPDF_ENABLE_AUTOLOAD', FALSE);

/**
 * A PDF generator plugin for the dompdf library.
 *
 * @PdfGenerator(
 *   id = "dompdf",
 *   module = "pdf_api",
 *   title = @Translation("dompdf"),
 *   description = @Translation("PDF generator using the DOMPDF generator."),
 *   required_class = "Dompdf\Dompdf",
 * )
 */
class DompdfGenerator extends PdfGeneratorBase implements ContainerFactoryPluginInterface {

  /**
   * Instance of the DOMPDF class library.
   *
   * @var \DOMPDF
   */
  protected $generator;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->generator = new Dompdf();
    $options = new Options([
      'isRemoteEnabled' => TRUE,
    ]);
    $this->generator->setOptions($options);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setter($pdf_content, $pdf_location, $save_pdf, $paper_orientation, $paper_size, $footer_content, $header_content, $path_to_binary = '') {
    $this->setPageOrientation($paper_orientation);
    $this->addPage($pdf_content);
    $this->setHeader($header_content);
  }

  /**
   * {@inheritdoc}
   */
  public function getObject() {
    return $this->generator;
  }

  /**
   * {@inheritdoc}
   */
  public function setHeader($text) {
    if (!$text) {
      return;
    }

    $canvas = $this->generator->get_canvas();
    $canvas->page_text(72, 18, $text, "", 11, [0, 0, 0]);
  }

  /**
   * {@inheritdoc}
   */
  public function addPage($html) {
    $this->generator->loadHtml($html);
    $this->generator->render();
    if (is_array($GLOBALS['_dompdf_warnings'])) {
      foreach ($GLOBALS['_dompdf_warnings'] as $warning) {
        \Drupal::logger('pdf api')->warning($warning);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPageOrientation($orientation = PdfGeneratorInterface::PORTRAIT) {
    $this->generator->setPaper("", $orientation);
  }

  /**
   * {@inheritdoc}
   */
  public function setPageSize($page_size) {
    if ($this->isValidPageSize($page_size)) {
      $this->generator->setPaper($page_size);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setFooter($text) {
    // @todo see issue over here: https://github.com/dompdf/dompdf/issues/571
  }

  /**
   * {@inheritdoc}
   */
  public function save($location) {
    $content = $this->generator->output([]);
    file_put_contents($location, $content);
  }

  /**
   * {@inheritdoc}
   */
  public function send() {
    $this->generator->stream("pdf", ['Attachment' => 0]);
  }

  /**
   * {@inheritdoc}
   */
  public function stream($filelocation) {
    $this->generator->Output($filelocation, "F");
  }

}
