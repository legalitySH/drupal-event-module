<?php

declare(strict_types=1);

namespace Drupal\events_management;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\events_management\Dto\UserEventRegisterDto;
use Drupal\node\Entity\Node;

class EventRegistrationService {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager
  ) {
  }

  /**
   * @throws EntityStorageException
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   * @throws \Exception
   */
  public function registerUserOnEvent(UserEventRegisterDto $dto): void {
    $event = $this->entityTypeManager->getStorage('node')->load($dto->eventId);

    if (!$event || $event->getType() !== 'event') {
      throw new \Exception('Event not found');
    }

    $maxParticipants = $event->get('field_max_participants')->value;

    $isRegistered = $this->entityTypeManager->getStorage('node')
      ->loadByProperties([
        'type' => 'event_guest',
        'field_event_id' => $dto->eventId,
        'field_guest_uuid' => $dto->user->id(),
      ]);

    if (!empty($isRegistered)) {
      throw new \Exception("You're already registered on this event!");
    }

    $currentParticipants = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['type' => 'event_guest', 'field_event_id' => $dto->eventId]);

    if (count($currentParticipants) >= $maxParticipants || $event->get('field_event_status') === 'Completed') {
      throw new \Exception('Max participants reached or registration closed');
    }

    $registration = Node::create([
      'type' => 'event_guest',
      'status' => FALSE,
      'title' => md5((string) $dto->user->id()),
      'field_event_id' => $dto->eventId,
      'field_guest_uuid' => $dto->user->id(),
      'field_guest_name' => $dto->name,
      'field_guest_email' => $dto->email,
    ]);

    $registration->save();

    if (count($currentParticipants) + 1 === (int)$maxParticipants) {
      $event->set('field_event_status', 'Completed');
      $event->save();
    }

  }
}
