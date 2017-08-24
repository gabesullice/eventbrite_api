<?php

namespace Drupal\eventbrite_api\Iterator;

use Drupal\eventbrite_api\HttpClientInterface;

class RequestIterator implements \Iterator {

  /**
   * The Eventbrite API client.
   *
   * @var \Drupal\eventbrite_api\HttpClientInterface
   */
  protected $client;

  /**
   * The HTTP method of the request.
   *
   * @var string
   */
  protected $method;

  /**
   * The endpoint over which to iterate.
   *
   * @var string
   */
  protected $endpoint;

  /**
   * Custom query parameters for the requests.
   *
   * @var array
   */
  protected $query;

  /**
   * The current page.
   *
   * @var int
   */
  protected $page;

  /**
   * The total pages available.
   *
   * @var int
   */
  protected $pageCount;

  /**
   * The current response.
   */ 
  protected $response;

  public function __construct(HttpClientInterface $http_client, $method, $endpoint, $query) {
    $this->client = $http_client;
    $this->method = $method;
    $this->endpoint = $endpoint;
    $this->query = $query;
  }

  public static function create(HttpClientInterface $http_client, $method, $endpoint, $query) {
    return new static($http_client, $method, $endpoint, $query);
  }

  public function current() {
    return $this->response;
  }

  public function key() {
    return $this->page - 1;
  }

  public function next() {
    $this->page += 1;
    if ($this->validPage($this->page)) {
      $this->setResponse($this->page);
    }
    else {
      $this->response = NULL;
    }
  }

  public function rewind() {
    $this->page = 1;
    $this->setResponse($this->page);
  }

  public function valid() {
    if (!$this->validPage($this->page)) return FALSE;
    $code = $this->response['code'];
    return $code >= 200 && $code < 300;
  }

  protected function setResponse($page) {
    $query = is_array($this->query) ? $this->query : [];
    $params = array_merge($query, ['page' => $page]);
    $this->response = $this->client->makeRequest(
      $this->method,
      $this->endpoint,
      $params
    );
    $this->setPageCount($this->response);
  }

  protected function setPageCount($response) {
    if (isset($response['body'])) {
      if (isset($response['body']['pagination'])) {
        $pagination = $response['body']['pagination'];
        if (isset($pagination['page_count'])) {
          $this->pageCount = $pagination['page_count'];
        }
      }
    }
  }

  protected function validPage($page) {
    return (!is_null($this->pageCount)) ? $page <= $this->pageCount : TRUE;
  }

}
