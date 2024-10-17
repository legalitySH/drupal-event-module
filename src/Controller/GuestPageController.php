<?php declare(strict_types = 1);

namespace Drupal\events_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\events_management\Entity\EventInterface;

final class GuestPageController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function view(EventInterface $event): array
  {
    return $build['content'] = [
      '#theme' => 'item_list',
      '#items' => $event->getGuestList(),
      '#title' => $this->t('Guests for Event : @event_title', ['@event_title' => $event->get('title')->value]),
    ];
  }
}
