<?php
/**
 * @file
 * Populate content for entities.
 */

class defaultContentFieldCollection extends defaultContentBaseMigration {

  public function __construct(array $arguments) {
    $this->arguments = $arguments;
    parent::__construct($arguments);

    $this->addDefaultMappings();

    $this->map = new MigrateSQLMap($this->machineName,
      array(
        'id' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ),
      ),
      MigrateDestinationFieldCollection::getKeySchema()
    );
    // Create a MigrateSource object.
    $this->source = $this->getMigrationSource();
    $this->destination = new MigrateDestinationFieldCollection($this->migrationData['field_name'], array('host_entity_type' => $this->migrationData['host_entity_type']));

  }

  /**
   * Implements prepareRow.
   * Fixes host_entity_id
   */
  function prepareRow($row) {
    if (!empty($row->host_entity_id)) {
      $host_legacy_id = $row->host_entity_id;
      $source_migration = Migration::getInstance($this->migrationData['host_migration']);
      $host_nid = $source_migration->getMap()->lookupDestinationID(array($host_legacy_id));
      $host_nid = array_pop($host_nid);
      $row->host_entity_id = $host_nid;
    }
  }
}

