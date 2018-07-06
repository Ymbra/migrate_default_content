<?php

namespace Drupal\migrate_default_content;

class BaseSourcePlugin implements SourcePluginInterface {

  /**
   * The migration ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The header of the data source.
   *
   * @var array
   */
  protected $header;

  /**
   * The full path of the file.
   *
   * @var string
   */
  protected $fullPathFile;

  /**
   * The langcode of the source migration.
   *
   * @var string
   */
  protected $language;

  /**
   * The entity type represented in the current source.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The bundle represented in the current source.
   *
   * @var string
   */
  protected $bundle;

  /**
   * BaseSourcePlugin constructor.
   *
   * @param array $configuration
   *   Entire configuration for data source plugin.
   */
  public function __construct(array $configuration) {
    $this->header = NULL;
    $this->language = '';

    // Extract some information in the filename.
    $fileparts = explode('.', $configuration['filename']);
    $this->entityType = $fileparts[0];
    $this->bundle = $fileparts[1];
    // If the file is a translation save the language that is informed in
    // the filename before the file extension.
    if (isset($fileparts[3])) {
      $this->language = $fileparts[2];
    }
    $this->fullPathFile = $configuration['source_dir'] . '/' . $configuration['filename'];

    // Generate the migration ID.
    $this->id = 'mdc_' . $this->getEntityType() . '_' . $this->getBundle();
    if (!empty($this->getLanguage())) {
      $this->id .= '_' . $this->getLanguage();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getFullPathFile() {
    return $this->fullPathFile;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeader() {
    return $this->header;
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    // Assume the key is the first field of the file.
    return trim(reset($this->getHeader()));
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguage() {
    return $this->language;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityType() {
    return $this->entityType;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundle() {
    return $this->bundle;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceMigrationDefinition() {
    return [];
  }

}
