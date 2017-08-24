<?php

namespace Drupal\eventbrite_api;

interface HttpClientInterface {

  /**
   * A slightly abstracted wrapper around call().
   *
   * This essentially splits the call options array into different parameters
   * to make it more obvious to less advanced users what parameters can be
   * passed to the client.
   *
   * @param string $verb
   * @param string $endpoint
   * @param null   $params
   * @param null   $body
   * @param null   $headers
   * @param array  $options
   *
   * @return array|mixed|\Psr\Http\Message\ResponseInterface
   * @throws \Exception
   */
  public function makeRequest($verb, $endpoint, $params = null, $body = null, $headers = null, $options = []);

}
