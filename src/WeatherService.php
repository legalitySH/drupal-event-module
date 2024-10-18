<?php

declare(strict_types=1);

namespace Drupal\events_management;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class WeatherService
{
  public function __construct(protected readonly ClientInterface $client)
  {
  }

  /** @return array<string,mixed> | null
   * @throws GuzzleException
   */
  public function getWeatherData(int $event_id, float $latitude, float $longitude): ?array
  {
    try {
      $response = $this->client->request(
        'GET',
        "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current=temperature_2m&hourly=temperature_2m"
      );

      return json_decode($response->getBody()->getContents(), true);
    } catch (RequestException $e) {
      \Drupal::logger('events_management')->error($e->getMessage());
      return null;
    }
  }
}
