<?php
/**
 * @file
 * Populate content for entities.
 */

class defaultContentEntityWithStub extends defaultContentEntity {

  /**
   * Create a node stub to be able to relate content with itself.
   *
   * @see https://drupal.org/node/1013506
   * @TODO: This should be an entity instead.
   */
  protected function createStub(Migration $migration, array $source_id) {
    $node = new stdClass();
    $node->title = t('Stub for @id', array('@id' => $source_id[0]));
    $node->body[LANGUAGE_NONE][0] = t('Stub body');
    $node->type = $this->destination->getBundle();
    $node->uid = 1;
    $node->status = 0;
    node_save($node);
    if (isset($node->nid)) {
      return array($node->nid);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Prepare the row to be sure we've got both id and revision id to correctly
   * update the content.
   */
  public function prepareRow($row) {
    if (isset($row->migrate_map_destid1) && is_null($row->migrate_map_destid2)) {
      $destination = $this->getDestination();
      $info = entity_get_info($destination->getEntityType());
      $entity = entity_load_single($destination->getEntityType(), $row->migrate_map_destid1);
      $row->migrate_map_destid2 = $entity->{$info['entity keys']['revision']};
    }
  }

}
