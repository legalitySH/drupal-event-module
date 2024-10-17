<?php

declare(strict_types=1);

namespace Drupal\events_management\Plugin\GraphQL\DataProducer;

use Drupal\graphql\Annotation\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "format_date",
 *   name = @Translation("Format date"),
 *   description = @Translation("Formats a timestamp into a readable date."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Formatted date")
 *   ),
 *   consumes = {
 *     "timestamp" = @ContextDefinition("string",
 *       label = @Translation("Timestamp")
 *     ),
 *     "format" = @ContextDefinition("string",
 *       label = @Translation("Date format"),
 *       default_value = "Y-m-d H:i:s"
 *     )
 *   }
 * )
 */
final class FormatDate extends DataProducerPluginBase {

  public function resolve(string $timestamp, $format = 'Y-m-d H:i:s'): string {
    return date($format, (int) $timestamp);
  }
}
