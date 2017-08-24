<?php

namespace Drupal\eventbrite_api\Client;

use Drupal\Core\Cache\CacheBackendInterface; 
use Drupal\eventbrite_api\HttpClientInterface;

class CachedHttpClient implements HttpClientInterface {

  /**
   * The Eventbrite API client.
   *
   * @var \Drupal\eventbrite_api\HttpClientInterface
   */
  protected $client;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The length of time for which a cached item should be valid.
   *
   * @var integer
   */
  protected $cacheDuration;

  /**
   * {@inheritdoc}
   */
  public function __construct(HttpClientInterface $client, CacheBackendInterface $cache) {
    $this->client = $client;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(HttpClientInterface $client, CacheBackendInterface $cache) {
    return new static($client, $cache);
  }

  /**
   * Sets the cache duration.
   */
  public function setCacheDuration($duration) {
    $this->cacheDuration = $duration;
  }

  /**
   * {@inheritdoc}
   */
  public function makeRequest($verb, $endpoint, $params = null, $body = null, $headers = null, $options = []) {
    $cid = hash('sha256', serialize(func_get_args()));

    if ($cached = $this->cache->get($cid)) {
      $result = unserialize($cached->data);
    } else {
      $result = $this->client->makeRequest($verb, $endpoint, $params, $body, $headers, $options);
      $cache_time = time() + $this->cacheDuration;
      $this->cache->set($cid, serialize($result), $cache_time);
    }

    return $result;
  }

}
