<?php

namespace Drupal\migrate_default_content;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a source plugin manager for data source for migrations.
 */
class SourcePluginManager extends DefaultPluginManager {

  /**
   * Construct a new SourcePluginManager object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/MigrateDefaultContent/Source', $namespaces, $module_handler, 'Drupal\migrate_default_content\SourcePluginInterface', 'Drupal\migrate_default_content\Annotation\Source');

    $this->alterInfo('migrate_default_content_source');
    $this->setCacheBackend($cache_backend, 'migrate_default_content_source_plugin');
  }

}
