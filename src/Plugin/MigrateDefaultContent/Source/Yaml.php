<?php

namespace Drupal\migrate_default_content\Plugin\MigrateDefaultContent\Source;

use Symfony\Component\Yaml\Parser;
use Drupal\migrate_default_content\BaseSourcePlugin;

/**
 * Defines a Yaml data source implementation.
 *
 * @Source(
 *   id = "yaml",
 *   extension = "yml"
 * )
 */
class Yaml extends BaseSourcePlugin {

  /**
   * Yaml constructor.
   *
   * @param array $configuration
   *   Entire configuration for data source plugin.
   */
  public function __construct(array $configuration) {
    parent::__construct($configuration);

    // Initialize header.
    $this->header = [];
    $parser = new Parser();
    $data = $parser->parse(file_get_contents($this->getFullPathFile()));
    foreach ($data as $item) {
      $this->header = array_merge($this->header, array_diff(array_keys($item), $this->header));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceMigrationDefinition() {
    return [
      'plugin' => 'yaml',
      'file' => $this->getFullPathFile(),
      'ids' => [
        $this->getKey() => [
          'type' => 'string',
        ],
      ],
      'fields' => array_combine($this->getHeader(), $this->getHeader()),
      'constants' => [
        'langcode' => $this->getLanguage(),
        // The source language will be the default for the site.
        'source_langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
      ],
    ];
  }

}
