<?php

namespace Drupal\migrate_default_content\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Source for YAML.
 *
 * If the YAML file contains non-ASCII characters, make sure it includes a
 * UTF BOM (Byte Order Marker) so they are interpreted correctly.
 *
 * @MigrateSource(
 *   id = "yaml"
 * )
 */
class YAML extends SourcePluginBase {

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
   * The file class to read the file.
   *
   * @var string
   */
  protected $fileClass = '';
  
  /**
   * The human-readable column headers, keyed by column index in the CSV.
   *
   * @var array
   */
  protected $columnNames = [];

  /**
   * The file object that reads the YAML file.
   *
   * @var \SplFileObject
   */
  protected $file = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    // Path is required.
    if (empty($this->configuration['path'])) {
      throw new MigrateException('You must declare the "path" to the source YAML file in your source settings.');
    }

    // Key field(s) are required.
    if (empty($this->configuration['keys'])) {
      throw new MigrateException('You must declare "keys" as a unique array of fields in your source settings.');
    }

//     $this->fileClass = empty($configuration['file_class']) ? 'Drupal\migrate_source_YAML\YAMLFileObject' : $configuration['file_class'];
  }

  /**
   * Return a string representing the source file path.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return $this->configuration['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {

    $parser = new \Symfony\Component\Yaml\Parser();
    $ret = new \ArrayIterator(
        $parser->parse(file_get_contents($this->configuration['path']))
    );
    
    if (empty($this->configuration['column_names'])) {
        foreach (array_keys($ret->offsetGet(0)) as $header) {        
            $header = trim($header);
            $this->columnNames[] = [$header => $header];
        }
        $ret->rewind();
    }
    else {
    
       $this->columnNames = $this->configuration['column_names'];
    }
    
    return $ret;
    /*
    // File handler using header-rows-respecting extension of SPLFileObject.
    $this->file = new $this->fileClass($this->configuration['path']);

    // Set basics of YAML behavior based on configuration.
    $delimiter = !empty($this->configuration['delimiter']) ? $this->configuration['delimiter'] : ',';
    $enclosure = !empty($this->configuration['enclosure']) ? $this->configuration['enclosure'] : '"';
    $escape = !empty($this->configuration['escape']) ? $this->configuration['escape'] : '\\';
    $this->file->setYAMLControl($delimiter, $enclosure, $escape);

    // Figure out what YAML column(s) to use. Use either the header row(s) or
    // explicitly provided column name(s).
    if (!empty($this->configuration['header_row_count'])) {
      $this->file->setHeaderRowCount($this->configuration['header_row_count']);

      // Find the last header line.
      $this->file->rewind();
      $this->file->seek($this->file->getHeaderRowCount() - 1);

      $row = $this->file->current();
      foreach ($row as $header) {
        $header = trim($header);
        $column_names[] = [$header => $header];
      }
      $this->file->setColumnNames($column_names);
    }
    // An explicit list of column name(s) will override any header row(s).
    if (!empty($this->configuration['column_names'])) {
      $this->file->setColumnNames($this->configuration['column_names']);
    }

    return $this->file;
    */
  }

  /**
   * {@inheritdoc}
   */
  public function getIDs() {
    $ids = [];
    foreach ($this->configuration['keys'] as $key) {
      $ids[$key]['type'] = 'string';
    }
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [];
    foreach ($this->columnNames as $column) {
      $fields[key($column)] = reset($column);
    }

    // Any caller-specified fields with the same names as extracted fields will
    // override them; any others will be added.
    if (!empty($this->configuration['fields'])) {
      $fields = $this->configuration['fields'] + $fields;
    }

    return $fields;
  }

}
