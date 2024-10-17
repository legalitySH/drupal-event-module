<?php

namespace Drupal\events_management\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cron;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\events_management\WeatherService;

/**
 * Provides a 'Weather Block' block.
 *
 * @Block(
 *   id = "weather_block",
 *   admin_label = @Translation("Weather Block"),
 *   category = @Translation("Custom"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Event node"), required = FALSE)
 *   }
 * )
 */
class WeatherBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  protected readonly ?WeatherService $weather_service;

  public static function create(
    ContainerInterface $container,
    array $configuration, $plugin_id, $plugin_definition
  ): static {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->weather_service = $container->get('events_management.weather_service');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array
  {
    $node = \Drupal::routeMatch()->getParameter('node');

    if ($node && $node->hasField('field_location')) {
      $location = $node->get('field_location')->getValue();
      if (!empty($location)) {
        $latitude = $location[0]['lat'];
        $longitude = $location[0]['lon'];

        $build['#weather'] = $this->weather_service->getWeatherData($node->id(), $latitude, $longitude);

        $build['#theme'] = 'weather-block';
        $build['#cache'] = [
          'max-age' => 0
        ];

        return $build;
      }
    }

    return [
      '#markup' => $this->t('Unable to fetch weather data.'),
    ];
  }
}
