<?php

declare(strict_types=1);

namespace Drupal\events_management;

use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class WeatherService {
  public function __construct(
    protected readonly ClientInterface $client,
    protected readonly CacheBackendInterface $cache,
  ) {
  }

  /** @return array<string,mixed> | null
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getWeatherData(int $event_id, float $latitude, float $longitude): ?array {
    $cache_id = "weather_data_$event_id:" . $latitude . ':' . $longitude;

    $cached_data = $this->cache->get($cache_id);
    if ($cached_data && isset($cached_data->data)) {
      return $cached_data->data;
    }

    try {
      $response = $this->client->request(
        'GET',
        "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current=temperature_2m&hourly=temperature_2m"
      );

      $data = json_decode($response->getBody()->getContents(), true);
      $this->cache->set($cache_id, $data, strtotime('+10 minutes'));

      if (!is_array($data)) {
        return null;
      }

      return $data;
    } catch (RequestException $e) {
      \Drupal::logger('events_management')->error($e->getMessage());
      return null;
    }
  }
}
