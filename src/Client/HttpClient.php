<?php

namespace Drupal\eventbrite_api\Client;

use jamiehollern\eventbrite\Eventbrite;
use Drupal\eventbrite_api\HttpClientInterface;

class HttpClient extends Eventbrite implements HttpClientInterface {

  /**
   * Create a new client instance.
   *
   * @param string $token
   *   The Eventbrite OAuth token.
   */
  public static function create($token) {
    return new static($token);
  }

}
