<?php

declare(strict_types=1);

namespace Drupal\events_management\Plugin\GraphQL\DataProducer;

use Drupal\Core\Annotation\ContextDefinition;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\events_management\Dto\UserEventRegisterDto;
use Drupal\events_management\EventRegistrationService;
use Drupal\graphql\Annotation\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "event_registration",
 *   name = @Translation("Event registration"),
 *   description = @Translation("Register user on event"),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Registration result")
 *   ),
 *   consumes = {
 *     "event_id" = @ContextDefinition("integer",
 *        label = @Translation("Event ID")
 *     ),
 *     "uid" = @ContextDefinition("integer",
 *        label = @Translation("Participant UID")
 *     ),
 *     "name" = @ContextDefinition("string",
 *        label = @Translation("Participant name")
 *      ),
 *      "email" = @ContextDefinition("string",
 *        label = @Translation("Participant email")
 *      ),
 *   }
 * )
 */
class EventRegistration extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  protected readonly ?EventRegistrationService $event_registration_service;
  protected readonly EntityTypeManagerInterface $entity_type_manager;

  public static function create(
    ContainerInterface $container,
    array $configuration, $plugin_id, $plugin_definition
  ): static {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->event_registration_service = $container->get('events_management.event_registration_service');
    $instance->entity_type_manager = $container->get('entity_type.manager');

    return $instance;
  }

  public function resolve (
    int $event_id,
    int $uid,
    string $name,
    string $email
  ): array {
    try {
      $user = User::load($uid);

      \Drupal::logger('events_management')->notice(json_encode($user, JSON_PRETTY_PRINT));
      if (!$user) {
        throw new \Exception('User not found');
      };

      $this->event_registration_service->registerUserOnEvent(new UserEventRegisterDto(
        (string) $event_id,
        $user,
        $name,
        $email
      ));

      return [
        'success' => TRUE,
        'message' => 'User registered successfully',
      ];
    } catch (\Exception $exception) {
      return [
        'success' => FALSE,
        'message' => $exception->getMessage(),
      ];
    }
  }
}
