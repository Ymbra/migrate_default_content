<?php
/**
 * @file
 * Populate content for entities.
 */

class defaultEntity extends defaultBaseMigration {

  public function __construct(array $arguments) {
    parent::__construct($arguments);

    $this->description = t('Import @type - @bundle from file.', array('@type' => $this->migrationData['entity_type'], '@bundle' => $this->migrationData['bundle']));
    $this->addDefaultMappings();

    // Create a MigrateSource object.
    $this->source = $this->getMigrationSource();
    $this->destination = new MigrateDestinationEntityAPI($this->migrationData['entity_type'], $this->migrationData['bundle'], array('text_format' => 'filtered_html'));

    // Create a map object for tracking the relationships between source rows.
    $this->map = new MigrateSQLMap($this->machineName,
      array(
        'id' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ),
      ),
      $this->destination->getKeySchema($this->migrationData['entity_type'], $this->migrationData['bundle']));

  }

  /**
   * Adds support for entityqueue via a "entityqueue" parameter containing entityqueue name.
   *
   * @fixme: add support for custom hooks, add this via a migrate_default_content_extras.
   */
  function complete($entity, stdClass $row) {
    $migrationData = $this->migrationData;
    // Parameter entityque is present.
    if (!empty($migrationData['entityqueue'])
      && (module_exists('entityqueue'))
      // Entity type for the element being migrated
      && (!empty($migrationData['entity_type']))
      // Loads queue as stated on parameters.
      && ($queue = entityqueue_subqueue_load($migrationData['entityqueue']))
      // Check if our entity is supported by this queue.
      && ($field_name = _entityqueue_get_target_field_name($migrationData['entity_type']))
      && ($instances = array_key_exists($field_name, field_info_instances('entityqueue_subqueue', $queue->queue)))
    ) {
      // Getting id for current entity
      list($id) = entity_extract_ids($migrationData['entity_type'], $entity);
      // Wrapper for entityqueue.
      $wrapper = entity_metadata_wrapper('entityqueue_subqueue', $queue);
      // Loading entity into entityqueue.
      $wrapper->{$field_name}[] = $id;
      $wrapper->save();
    }
  }

}
