<?php

namespace Drupal\eventbrite_api\Iterator;

class ResponseIterator implements \Iterator {

  /**
   * The current page.
   *
   * @var int
   */
  protected $position;

  /**
   * The Eventbrite API type.
   *
   * @var string
   */ 
  protected $type;

  /**
   * The underlying RequestIterator.
   *
   * @var \Iterator
   */ 
  protected $requestIterator;

  /**
   * The item iterator.
   *
   * @var \Iterator
   */ 
  protected $iterator;

  public function __construct($type, \Iterator $iterator) {
    $this->type = $type;
    $this->requestIterator = $iterator;
    $this->position = 0;
  }

  public static function create($type, \Iterator $iterator) {
    return new static($type, $iterator);
  }

  public function current() {
    return $this->iterator->current();
  }

  public function key() {
    return $this->position;
  }

  public function next() {
    $this->position += 1;
    $this->iterator->next();
    if (!$this->iterator->valid()) {
      $this->requestIterator->next();
      $this->updateIterator();
    }
  }

  public function rewind() {
    $this->position = 0;
    $this->requestIterator->rewind();
    $this->updateIterator();
  }

  public function valid() {
    return $this->iterator->valid();
  }

  protected function updateIterator() {
    if ($this->requestIterator->valid()) {
      $response = $this->requestIterator->current();
      $this->iterator = $this->getIterator($response);
    }
    else {
      $this->iterator = new \EmptyIterator();
    }
  }

  protected function getIterator($response) {
    switch ($this->type) {
      case 'event':
        return new \ArrayIterator($response['body']['events']);
      default:
        return new \ArrayIterator($response['body']);
    }
  }

}
