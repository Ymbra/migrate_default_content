<?php
/**
 * @file
 * Populate content for comments.
 */

/**
 * Class to manage comment migrations.
 */
class defaultComment extends defaultEntity {

  /**
   * Fill the $entity->nid that is expected from drupal comment core module.
   */
  public function prepare($entity, stdClass $row) {
    $entity->nid = reset($entity->node);
  }

}
