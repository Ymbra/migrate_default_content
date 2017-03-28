<?php

namespace Drupal\migrate_default_content\Plugin\migrate\source;

use ArrayIterator;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlComponent;

/**
 * Migrate source plugin for Yaml.
 *
 * Originally created by Stefan Borchert (stBorchert) on d8__migrate_source_yaml
 * (https://www.drupal.org/sandbox/stborchert/2808617)
 *
 * Based on D7 module https://www.drupal.org/project/migrate_source_yaml
 *
 * @MigrateSource(
 *   id = "yaml"
 * )
 */
class Yaml extends SourcePluginBase {

  /**
   * Data obtained from the YAML file.
   *
   * @var array[]
   *   Array of data rows, each one an array of values keyed by field names.
   */
  protected $dataRows = [];

  /**
   * List of available source fields.
   *
   * Keys are the field machine names as used in field mappings, values are
   * descriptions.
   *
   * @var array
   */
  protected $fields = [];

  /**
   * List of key fields, as indexes.
   *
   * @var array
   */
  protected $keys = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    // Path is required.
    if (empty($this->configuration['file'])) {
      throw new MigrateException('You must declare the "file" to the source Yaml file in your source settings.');
    }

    // ID field(s) are required.
    if (empty($this->configuration['ids'])) {
      throw new MigrateException('You must declare "ids" as a unique array of fields in your source settings.');
    }

    try {
      $this->dataRows = YamlComponent::parse(file_get_contents($configuration['file']));
    }
    catch (ParseException $exc) {
      \Drupal::logger('Migrate source Yaml')->error('Failed to parse source file @file', ['@file' => $configuration['file']]);
    }
    $this->ids = $configuration['ids'];
  }

  /**
   * Return a string representing the source query.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return $this->configuration['file'];
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    return new ArrayIterator($this->dataRows);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return $this->ids;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return count($this->dataRows);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return empty($this->configuration['fields']) ? [] : $this->configuration['fields'];
  }

}
