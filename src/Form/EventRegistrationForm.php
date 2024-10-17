<?php

declare(strict_types=1);

namespace Drupal\events_management\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\events_management\Dto\UserEventRegisterDto;
use Drupal\events_management\EventRegistrationService;

/**
 * Form controller for the event registration form.
 */
class EventRegistrationForm extends FormBase {

  private readonly ?EventRegistrationService $registration_service;
  private readonly EntityTypeManagerInterface $entity_type_manager;

  public static function create(ContainerInterface $container): EventRegistrationForm {
    $instance = parent::create($container);
    $instance->registration_service = $container->get('events_management.event_registration_service');
    $instance->entity_type_manager = $container->get('entity_type.manager');

    return $instance;
  }

  public function access(): AccessResult {
    $event_id = $this->getEventId();
    $event = $this->entity_type_manager->getStorage('node')->load($event_id);

    if (
      !$event ||
      $event->bundle() !== 'event' ||
      $event->get('field_event_status')->value === 'Completed'
    ) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  public function getFormId(): string {
    return 'events_management_event_registration';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $eventId = $this->getEventId();
    $event = $this->entity_type_manager->getStorage('node')->load($eventId);

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email address'),
      '#required' => TRUE,
    ];

    $form['event_id'] = [
      '#type' => 'hidden',
      '#value' => $this->getEventId(),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();

    try {
      $this->registration_service->registerUserOnEvent(
        new UserEventRegisterDto(
          $values['event_id'],
          $this->currentUser(),
          $values['name'],
          $values['email']
        )
      );
    } catch (\Exception $e) {
      \Drupal::messenger()->addError($this->t('@message', ['@message' => $e->getMessage()]));
      return;
    }
    $form_state->setRedirect('entity.node.canonical', ['node' => $values['event_id']]);
    \Drupal::messenger()->addMessage($this->t('You have successfully registered for the event!'));
  }

  protected function getEventId() : mixed {
    $route_match = \Drupal::routeMatch();
    return $route_match->getParameter('event_id');
  }
}
