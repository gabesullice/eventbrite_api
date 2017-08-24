<?php

namespace Drupal\eventbrite_api; 

use Drupal\eventbrite_api\HttpClientInterface;
use Drupal\eventbrite_api\Iterator\MappingIterator;
use Drupal\eventbrite_api\Iterator\RequestIterator;
use Drupal\eventbrite_api\Iterator\ResponseIterator;

class DataProvider implements DataProviderInterface {

  /**
   * The Eventbrite API client.
   *
   * @var \Drupal\eventbrite_api\HttpClientInterface
   */
  protected $client;

  /**
   * HTTP methods that this class can currently handle.
   *
   * @var array
   */
  protected static $implementedMethods = [
    'GET',
  ];

  /**
   * Mapping of types to endpoints.
   *
   * @var array
   */
  protected static $endpoints = [
    'event' => 'events/:id/',
  ];

  /**
   * Mapping of types to their collection endpoints.
   *
   * @var array
   */
  protected static $multipleEndpoints = [
    'event' => 'users/me/owned_events/',
  ];

  public function __construct(HttpClientInterface $http_client) {
    $this->client = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public function get($type, $id, $query = NULL) {
    $endpoint = $this->getEndpoint('GET', $type);

    if (strpos($endpoint, ':id') !== FALSE) {
      $endpoint = str_replace(':id', $id, $endpoint);
    }

    if ($endpoint) {
      if ($data = $this->getData($this->makeRequest('GET', $endpoint, $query))) {
        return $this->getTypedData($data);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple($type, $query = NULL, $offset = 0, $count = -1) {
    $endpoint = $this->getMultipleEndpoint('GET', $type);

      if ($endpoint) {
        $client = $this->client;
        // Get an iterator over paginated requests.
        $requests = RequestIterator::create($client, 'GET', $endpoint, $query);
        // Cache the API requests for the duration of this script.
        $cached = new \CachingIterator($requests, \CachingIterator::TOSTRING_USE_KEY);
        // Map the responses to TypedData.
        $mapped = MappingIterator::create(
          // Create an iterator over the results of each request.
          ResponseIterator::create($type, $cached),
          // Maps the raw response data into TypedData. 
          function ($data) use ($type, $query) {
            return $this->getTypedData($data);
          } 
        );

        return new \LimitIterator($mapped, $offset, $count);
      }

    return new \EmptyIterator();
  }

  /**
   * Wraps call to client makeRequest.
   *
   * @see \Drupal\eventbrite_api\HttpClientInterface
   */
  protected function makeRequest($method, $endpoint, $params = NULL) {
    return $this->client->makeRequest($method, $endpoint, $params);
  }

  /**
   * Get the endpoint for a specific type.
   *
   * @param string $method
   *   The HTTP method.
   * @param string $type
   *   The data type for which to request data.
   *
   * @return string|NULL
   *   The endpoint path or NULL if one does not exist.
   */
  protected function getEndpoint($method, $type) {
    $methods = self::$implementedMethods;
    $types = array_keys(self::$endpoints);

    if (in_array($method, $methods) && in_array($type, $types)) {
      return self::$endpoints[$type];
    }

    return $type;
  }

  /**
   * Extracts the raw data from the client response.
   *
   * $param array $response
   *   The raw client response.
   */
  protected function getData($response) {
    $code = (integer) $response['code'];
    // If the request was successful.
    if ($code >= 200 && $code < 300) {
      return isset($response['body']) ? $response['body'] : NULL;
    }

    return NULL;
  }

  protected function getTypedData($data) {
    if (!$data) return NULL;
    // @todo: make this TypedData
    return $data;
  }

  /**
   * Get the endpoint for a specific type.
   *
   * Until more endpoints are supported, if the $type is not found,
   * it is simply returned as the endpoint value. This should be treated as
   * unstable behavior.
   *
   * @param string $method
   *   The HTTP method.
   * @param string $type
   *   The data type for which to request data.
   *
   * @return string|NULL
   *   The endpoint path or NULL if one does not exist.
   */
  protected function getMultipleEndpoint($method, $type) {
    $methods = self::$implementedMethods;
    $types = array_keys(self::$multipleEndpoints);

    if (in_array($method, $methods) && in_array($type, $types)) {
      return self::$multipleEndpoints[$type];
    }

    return $type;
  }

}
