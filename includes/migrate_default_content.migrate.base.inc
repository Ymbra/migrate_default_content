<?php


abstract class defaultContentBaseMigration extends DynamicMigration {

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
  public function getMigrationConfigData($machine_name) {
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
}
