<?php
/**
 * @file
 * Populate content for Organic Groups Membeship.
 */

class defaultOgMembership extends defaultBaseMigration {

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
      MigrateDestinationOGMembership::getKeySchema()
    );
    // Create a MigrateSource object.
    $this->source = $this->getMigrationSource();
    $this->destination = new MigrateDestinationOGMembership();
  }
}
