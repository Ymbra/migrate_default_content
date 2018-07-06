<?php

namespace Drupal\migrate_default_content;

/**
 * Defines the common interface for all SourcePlugin classes.
 */
interface SourcePluginInterface {

  /**
   * Get the migration ID.
   *
   * @return string
   */
  public function getId();

  /**
   * Get the migration language represented on the data source.
   *
   * @return array
   *   Array of field machine names.
   */
  public function getHeader();

  /**
   * Get the migration key represented on the data source.
   *
   * @return string
   *   Key of that migration.
   */
  public function getKey();

  /**
   * Get the migration language represented on the data source.
   *
   * @return string
   *   Langcode of the data source.
   */
  public function getLanguage();

  /**
   * Get the migration entity type represented on the data source.
   *
   * @return string
   *   Entity type machine name.
   */
  public function getEntityType();

  /**
   * Get the migration bundle represented on the data source.
   *
   * @return string
   *   Bundle machine name.
   */
  public function getBundle();

  /**
   * Returns the source definition to be added to the migration definition.
   *
   * @return array
   *   Array with source definition as expected by Migrate API.
   */
  public function getSourceMigrationDefinition();

}
