<?php
/**
 * @file
 * Populate content for entities.
 */

class defaultContentEntity extends defaultContentBaseMigration {

  public function __construct(array $arguments) {
    $this->arguments = $arguments;
    parent::__construct();

    // Make sure we can use it for node and term only.
    if (!in_array($this->migrationData['entity_type'], array('node', 'taxonomy_term'))) {
      throw new Exception('Migration supports only nodes and terms at the moment.');
    }

    $this->description = t('Import @type - @bundle from CSV file.', array('@type' => $this->migrationData['entity_type'], '@bundle' => $this->migrationData['bundle']));

    $this->csvColumns = !empty($this->csvColumns) ? $this->csvColumns : array();
    $csv_cols[] = array('id', 'Unique ID');

    switch ($this->migrationData['entity_type']) {
      case 'node':
        $this->addFieldMapping('title', 'title');
        $this->addFieldMapping('uid')->defaultValue(1);
        $class_name = 'MigrateDestinationNode';
        $csv_cols[] = array('title', 'Title');
        break;
      case 'taxonomy_term':
        $this->addFieldMapping('name', 'name');
        $class_name = 'MigrateDestinationTerm';
        $csv_cols[] = array('name', 'Name');
        break;
    }

    // Rebuild the csv columns array.
    $this->csvColumns = array_merge($csv_cols, $this->csvColumns);

    // Create a map object for tracking the relationships between source rows
    $key = array(
      'id' => array(
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
      ),
    );

    $destination_handler = new MigrateDestinationEntityAPI($this->migrationData['entity_type'], $this->migrationData['bundle']);
    $this->map = new MigrateSQLMap($this->machineName, $key, $destination_handler->getKeySchema($this->migrationData['entity_type']));

    // Create a MigrateSource object.
    $this->source = new MigrateSourceCSV($this->arguments['source_file'], $this->csvColumns, array('header_rows' => 1));
    $this->destination = new $class_name($this->migrationData['bundle'], array('text_format' => 'filtered_html'));
  }

}
