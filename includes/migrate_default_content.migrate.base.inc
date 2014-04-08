<?php


abstract class defaultBaseMigration extends DynamicMigration {

  /**
   * @var array $migrationData
   *  Raw array of migration files loaded from the yaml files.
   */
  protected $migrationData = array();
  /**
   * @var array $arguments
   *  Arguments passed when registering the migration.
   *  @see migrate_default_content_flush_caches()
   */
  protected $arguments;

  protected $mappedFields;

  public function __construct() {
    parent::__construct();
    $this->migrationData = $this->getMigrationConfigData($this->arguments['machine_name']);;
  }

  /**
   * Returns the corresponding migrate information among the yaml files in the
   * folder.
   *
   * @param string $machine_name
   *  The migration machine name defined in the file
   *
   * @return array
   */
  protected function getMigrationConfigData($machine_name) {
    $config = migrate_default_content_get_config_files();
    foreach ($config as $data) {
      if ($data['name'] == $machine_name) {
        return $data;
      }
    }
  }

  /**
   * Generate migration machine name from the yaml file machine name.
   * We need to override this method so the class name is not used.
   * @see https://drupal.org/node/1006998
   */
  protected function generateMachineName($class_name = NULL) {
    return $this->arguments['machine_name'];
  }

  protected function getMigrationSource() {
    $this->csvColumns = !empty($this->csvColumns) ? $this->csvColumns : array();
    $csv_cols[] = array('id', 'Unique ID');

    foreach ($this->migrationData['fields'] as $field) {
      $key = key($field);
      $value = $field[$key];
      if (!empty($value['source'])) {
        $csv_cols[] = array($key, $value['source']);
      }
    }

    // Rebuild the csv columns array.
    $this->csvColumns = array_merge($csv_cols, $this->csvColumns);

    return new MigrateSourceCSV($this->arguments['source_path'] . '/' . $this->arguments['source_file'], $this->csvColumns, array('header_rows' => 1));
  }

  protected function addDefaultMappings() {
    foreach ($this->migrationData['fields'] as $field) {
      $key = key($field);
      $value = $field[$key];
      if (is_array($value) && count($value) == 1) {
        $this->addFieldMapping($key, $key);
      }
      else {
        $this->preprocessMapping($key, $value);
      }
    }
  }

  private function preprocessMapping($key, $value) {
    if (!is_array($value)) {
      return;
    }

    // Handle default mapping, if there's any.
    if (!empty($value['source'])) {
      $this->addFieldMapping($key, $key);
    }

    // Handle empty, default values.
    if (!empty($value['default_value'])) {
      $migrate = $this->addFieldMapping($key);
      $migrate->defaultValue($value['default_value']);
    }

    // Split values.
    if (!empty($value['separator'])) {
      $migrate = $this->addFieldMapping($key, $key);
      $migrate->separator($value['separator']);
    }

    if (!empty($value['components']) && is_array($value['components'])) {
      foreach ($value['components'] as $name => $component) {
        if ($name == 'source_dir') {
          $component = $this->arguments['source_path'] . '/' . $component;
        }
        $migrate = $this->addFieldMapping($key . ':' . $name);
        $migrate->defaultValue($component);
      }
    }

    if (!empty($value['source_migration'])) {
      if (empty($migrate) || !$migrate instanceof MigrateFieldMapping) {
        $migrate = $this->addFieldMapping($key, $key);
      }
      $migrate->sourceMigration(array($value['source_migration']));
    }
  }

}
