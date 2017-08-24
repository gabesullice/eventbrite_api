<?php

namespace Drupal\eventbrite_api;

interface DataProviderInterface {

  /**
   * Loads an individual item from the Eventbrite API.
   *
   * @param string $type
   *   The dat type to load.
   * @param integer $id
   *   The ID of the data to load.
   * @param array $query
   *   Custom query options.
   *
   * @return \Drupal\Core\TypedData\TypeDataInterface|NULL
   *   Returns typed data of the requested item or NULL if it doesn't exist.
   */
  public function get($type, $id, $query = NULL);

  /**
   * Loads many items from the Eventbrite API.
   *
   * @param string $type
   *   The dat type to load.
   * @param array $query
   *   Additional query values which can limit the returned items.
   * @param integer $offset
   *   A result from which to begin.
   * @param integer $count
   *   The number of results to fetch. -1 for no limit.
   *
   * @return Iterator
   *   An iterator which returns TypedDataInterfaces for the requested data.
   */
  public function getMultiple($type, $query = NULL, $offset = 0, $count = -1);

}
