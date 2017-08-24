<?php

namespace Drupal\eventbrite_api\Iterator;

class MappingIterator extends \IteratorIterator {

  /**
   * The map function.
   */
  protected $mapper;

  public function __construct(\Iterator $iterator, callable $mapper) {
    parent::__construct($iterator);
    $this->mapper = $mapper;
  }

  public static function create(\Iterator $iterator, callable $mapper) {
    return new static($iterator, $mapper);
  }

  public function current() {
    return call_user_func($this->mapper, parent::current());
  }

}
