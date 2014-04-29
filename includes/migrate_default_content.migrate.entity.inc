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

}
