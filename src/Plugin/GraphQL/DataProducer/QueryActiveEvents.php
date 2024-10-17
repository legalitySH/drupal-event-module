<?php

namespace Drupal\events_management\Plugin\GraphQL\DataProducer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Annotation\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "query_active_events",
 *   name = @Translation("Query active events"),
 *   description = @Translation("Loads active events."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Event Connection")
 *   ),
 *   consumes = {
 *     "offset" = @ContextDefinition("integer",
 *       label = @Translation("Offset"),
 *       required = FALSE,
 *       default_value = 0
 *     ),
 *     "limit" = @ContextDefinition("integer",
 *       label = @Translation("Limit"),
 *       required = FALSE,
 *       default_value = 10
 *     )
 *   }
 * )
 */
class QueryActiveEvents extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  protected EntityTypeManagerInterface $entity_type_manager;

  public static function create(
    ContainerInterface $container,
    array $configuration, $plugin_id, $plugin_definition
  ): static {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entity_type_manager = $container->get('entity_type.manager');

    return $instance;
  }

  public function resolve($offset = 0, $limit = 10): array {
    $events = $this->entity_type_manager->getStorage('node')
      ->loadByProperties([
        'type' => 'event',
        'field_event_status' => 'Active'
      ]);

    if (empty($events)) {
      return [
        'total_count' => 0,
        'events' => [],
      ];
    }

    $paged_events = array_slice($events, $offset, $limit);

    $event_data = [];
    foreach ($paged_events as $event) {
      $event_data[] = [
        'node' => $event,
      ];
    }

    return [
      'total_count' => count($events),
      'events' => $event_data,
    ];
  }



}
