<?php

declare(strict_types=1);

namespace Drupal\events_management\Dto;

use Drupal\Core\Session\AccountInterface;

final class UserEventRegisterDto {

  public function __construct(
    public string $eventId,
    public AccountInterface $user,
    public string $name,
    public string $email,
  ) {
  }
}
