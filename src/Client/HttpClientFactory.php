<?php

namespace Drupal\eventbrite_api\Client;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\key\KeyRepositoryInterface;

class HttpClientFactory {

  public static function createHttpClient(ConfigFactoryInterface $config_factory, KeyRepositoryInterface $keys) {
    $key = self::getKey($config_factory->get('eventbrite_api.settings'), $keys);
    return HttpClient::create($key);
  }

  public static function createCachedHttpClient(CacheBackendInterface $cache, ConfigFactoryInterface $config_factory, KeyRepositoryInterface $keys) {
    $config = $config_factory->get('eventbrite_api.settings');

    $client = HttpClient::create(self::getKey($config, $keys));

    if ($duration = $config->get('cache_duration')) {
      $client = CachedHttpClient::create($client, $cache);
      $client->setCacheDuration($duration);
    }

    return $client;
  }

  protected static function getKey($config, KeyRepositoryInterface $keys) {
    if ($api_key = $config->get('api_key')) {
      if ($key = $keys->getKey($api_key)) {
        return $key->getKeyValue();
      }
    }
    return "no_key_configured";
  }

}
