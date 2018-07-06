<?php

namespace Drupal\migrate_default_content;

/**
 * Defines an interface for the migration definition generator.
 */
interface MigrationGeneratorInterface {

  /**
   * Generates migration definitions from the existing default content sources.
   *
   * @param array $definitions
   *   (optional) The already existing migration definitions in the system.
   *
   * @return array
   *   The generated migration definitions array.
   */
  public function generateMigrations(array $definitions = []);

}
