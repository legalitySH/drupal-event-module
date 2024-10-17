<?php

declare(strict_types=1);

namespace Drupal\events_management\Entity;

use Drupal\node\Entity\Node;

class Event extends Node implements EventInterface
{
  public function getGuestList(): array
  {
    $guest_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'event_guest',
      'field_event_id' => $this->id(),
    ]);

    $guest_list = [];
    foreach ($guest_nodes as $guest) {
      $guest_list[] = $guest->get('field_guest_name')->value . ' (' . $guest->get('field_guest_email')->value . ')';
    }

    return $guest_list;
  }
}
