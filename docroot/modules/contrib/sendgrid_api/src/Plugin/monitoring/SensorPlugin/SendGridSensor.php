<?php

declare(strict_types = 1);

namespace Drupal\sendgrid_api\Plugin\monitoring\SensorPlugin;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\DependencyTrait;
use Drupal\key\KeyRepositoryInterface;
use Drupal\monitoring\Entity\SensorConfig;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checks if an API key is valid.
 *
 * @SensorPlugin(
 *   id = "sendgrid_key",
 *   label = @Translation("SendGrid API Key"),
 *   description = @Translation("Checks connectivity and validity of a SendGrid API key."),
 *   addable = TRUE,
 *   deriver = "Drupal\sendgrid_api\Plugin\Derivative\SendGridSensorDeriver"
 * )
 */
class SendGridSensor extends SensorPluginBase implements DependentPluginInterface {

  use DependencyTrait;

  /**
   * Repository for Key configuration entities.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepository;

  /**
   * SendGrid API client.
   *
   * @var \SendGrid
   */
  protected $sendGrid;

  /**
   * SendGridSensor constructor.
   *
   * @param \Drupal\monitoring\Entity\SensorConfig $sensorConfig
   *   Sensor config object.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\key\KeyRepositoryInterface $keyRepository
   *   Repository for Key configuration entities.
   * @param \SendGrid $sendGrid
   *   SendGrid API client.
   */
  public function __construct(SensorConfig $sensorConfig, $pluginId, $pluginDefinition, KeyRepositoryInterface $keyRepository, \SendGrid $sendGrid) {
    parent::__construct($sensorConfig, $pluginId, $pluginDefinition);
    $this->keyRepository = $keyRepository;
    $this->sendGrid = $sendGrid;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, SensorConfig $sensor_config, $plugin_id, $plugin_definition) {
    return new static(
      $sensor_config,
      $plugin_id,
      $plugin_definition,
      $container->get('key.repository'),
      $container->get('sendgrid_api.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    [1 => $keyId] = explode(':', $this->getPluginId(), 2);
    $key = $this->keyRepository->getKey($keyId);
    $this->addDependency('config', $key->getConfigDependencyName());
    return $this->dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultConfiguration() {
    return [
      'caching_time' => 3600,
      'value_type' => 'bool',
      'category' => 'SendGrid API',
      'settings' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $sensor_result) {
    $sensor_result->setValue(FALSE);
    $sensor_result->setExpectedValue(TRUE);

    [1 => $keyId] = explode(':', $this->getPluginId(), 2);

    $key = $this->keyRepository->getKey($keyId);
    $apiKey = $key->getKeyValue();
    $sendGrid = new \SendGrid($apiKey);

    try {
      $response = $sendGrid->client->user()->username()->get();
      $json = $response->body();
      $decoded = Json::decode($json);
      $userName = $decoded['username'] ?? NULL;
      if ($userName) {
        $sensor_result->setValue(TRUE);
        $sensor_result->setMessage('Logged in as @username.', [
          '@username' => $userName,
        ]);
      }
      else {
        $sensor_result->setMessage('Unexpected response from API');
      }
    }
    catch (\Exception $exception) {
      $sensor_result->setMessage(sprintf('Error: %s', $exception->getMessage()));
    }
  }

}
