<?php

namespace Drupal\migrate_default_content\Plugin\migrate\process;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Transform a content id to what the entity revisions field expects.
 *
 * @MigrateProcessPlugin(
 *   id = "content_id_to_revision_reference"
 * )
 */
class ContentIdToRevisionReference extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Load the entity revision id.
    $target_revision_id = $this
      ->entityTypeManager
      ->getStorage($this->configuration['target_entity_type'])
      ->load($value)
      ->getRevisionId();
    if ($this->configuration['lookup_id']) {
      return $target_revision_id;
    }
    else {
      // Build an array with the structure that entity reference revisions
      // is expecting.
      return [
        'target_id' => $value,
        'target_revision_id' => $target_revision_id,
      ];
    }
  }

}
