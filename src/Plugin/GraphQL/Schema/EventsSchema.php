<?php

namespace Drupal\events_management\Plugin\GraphQL\Schema;

use Drupal\graphql\Annotation\Schema;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;

/**
 * @Schema(
 *   id = "events_management",
 *   name = "Events Management Schema"
 * )
 */
class EventsSchema extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry(): ResolverRegistryInterface {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry();

    $this->addEventFields($registry, $builder);
    $this->addQueryFields($registry, $builder);
    $this->addMutationFields($registry, $builder);

    return $registry;
  }

  protected function addEventFields(ResolverRegistry $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('Event', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Event', 'title',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Event', 'description',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:node'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_event_description.value'))
    );

    $registry->addFieldResolver('Event', 'start_date',
      $builder->produce('format_date')
        ->map('timestamp', $builder->produce('property_path')
          ->map('type', $builder->fromValue('entity:node'))
          ->map('value', $builder->fromParent())
          ->map('path', $builder->fromValue('field_event_start_time.value'))
        )
        ->map('format', $builder->fromValue('Y-m-d H:i:s'))
    );

    $registry->addFieldResolver('Event', 'end_date',
      $builder->produce('format_date')
        ->map('timestamp', $builder->produce('property_path')
          ->map('type', $builder->fromValue('entity:node'))
          ->map('value', $builder->fromParent())
          ->map('path', $builder->fromValue('field_event_end_time.value'))
        )
        ->map('format', $builder->fromValue('Y-m-d H:i:s'))
    );

    $registry->addFieldResolver('Event', 'status',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:node'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_event_status.value'))
    );

    $registry->addFieldResolver('Event', 'location',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:node'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_location.value'))
    );

    $registry->addFieldResolver('Event', 'max_participants',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:node'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_max_participants.value'))
    );
  }

  protected function addQueryFields(ResolverRegistry $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('Query', 'event',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['event']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'activeEvents',
      $builder->produce('query_active_events')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
    );
  }

  protected function addMutationFields(ResolverRegistry $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('Mutation', 'registerForEvent',
      $builder->produce('event_registration')
        ->map('event_id', $builder->fromArgument('eventId'))
        ->map('uid', $builder->fromArgument('uid'))
        ->map('name', $builder->fromArgument('name'))
        ->map('email', $builder->fromArgument('email'))
    );
  }
}
