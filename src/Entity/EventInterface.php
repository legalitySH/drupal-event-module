<?php

declare(strict_types=1);

namespace Drupal\events_management\Entity;

interface EventInterface
{
  /** @return array<string> */
  public function getGuestList(): array;
}
