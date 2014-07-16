<?php
/**
 * @file
 * Populate content for field collections.
 */

class defaultFile extends defaultBaseMigration {

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
      MigrateDestinationFile::getKeySchema()
    );
    // Create a MigrateSource object.
    $this->source = $this->getMigrationSource();
    $this->destination = new MigrateDestinationFile($this->migrationData['bundle']);

  }

  protected function preprocessMapping($key, $value) {
    if ($key == 'source_dir') {
      $value['default_value'] = $this->arguments['source_path'] . '/' . $value['default_value'];
    }
    parent::preprocessMapping($key, $value);
  }


}
