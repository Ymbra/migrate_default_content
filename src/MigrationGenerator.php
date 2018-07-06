<?php

namespace Drupal\migrate_default_content;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\file\Plugin\Field\FieldType\FileItem;
use Symfony\Component\Yaml\Parser;

/**
 * Generates Migration definitions from existing default content files.
 */
class MigrationGenerator implements MigrationGeneratorInterface {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\migrate_default_content\SourcePluginManager definition.
   *
   * @var \Drupal\migrate_default_content\SourcePluginManager
   */
  protected $sourcePluginManager;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Entity\EntityFieldManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Drupal\Core\Field\FieldTypePluginManagerInterface definition.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypePluginManager;

  /**
   * The default content definitions source directory.
   *
   * @var string
   */
  protected $sourceDir;

  /**
   * the list of defined migrations.
   *
   * @var array
   */
  protected $migrations;

  /**
   * Constructs a new MigrationGenerator object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\migrate_default_content\SourcePluginManager $source_plugin_manager
   *   The migrate default content source plugin manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_plugin_manager
   *   The field type plugin manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, SourcePluginManager $source_plugin_manager, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, FieldTypePluginManagerInterface $field_type_plugin_manager) {
    $this->configFactory = $config_factory;
    $this->sourcePluginManager = $source_plugin_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->fieldTypePluginManager = $field_type_plugin_manager;
    $this->sourceDir = DRUPAL_ROOT . '/' . $config_factory->get('migrate_default_content.settings')->get('source_dir');
  }

  /**
   * {@inheritdoc}
   */
  public function generateMigrations(array $definitions = []) {
    $generated = [];

    foreach ($this->getMigrations() as $id => $migration) {
      $generated[$id] =  ($id === 'mdc_file') ? $this->fileMigration($migration) : $this->generateMigrationPlugin($migration);
    }

    return $generated;
  }

  /**
   * Loads all the valid and available migrations defined in the source folder.
   *
   * @return array
   *   Array containing all the migrations defined in the source directory.
   */
  protected function getMigrations() {
    if (!isset ($this->migrations)) {
      $migrations = [];
      if ($handle = opendir($this->sourceDir)) {
        while (($file = readdir($handle)) !== FALSE) {
          if ($file != "." && $file != "..") {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            foreach ($this->sourcePluginManager->getDefinitions() as $definition) {
              if ($definition['extension'] == $extension) {
                $config = ['source_dir' => $this->sourceDir, 'filename' => $file,];

                /** @var \Drupal\migrate_default_content\SourcePluginInterface $plugin */
                $plugin = $this->sourcePluginManager->createInstance($definition['id'], $config);
                $migrations[$plugin->getId()] = $plugin;
              }
            }
          }
        }
        closedir($handle);
      }

      // Add files migration.
      if (file_exists($this->sourceDir . '/files') && $handle = opendir($this->sourceDir . '/files')) {
        $data_rows = [];
        while (($file = readdir($handle)) !== FALSE) {
          if ($file != "." && $file != "..") {
            $data_rows[] = ['filename' => $file];
          }
        }
        $migrations['mdc_file'] = ['data_rows' => $data_rows,];
        closedir($handle);
      }

      $this->migrations = $migrations;
    }

    return $this->migrations;
  }

  protected function fileMigration(array $migration) {
    return [
      'id' => 'mdc_file',
      'migration_tags' => ['migrate_default_content'],
      'label' => 'mdc_file',
      'class' => '\Drupal\migrate\Plugin\Migration',
      'status' => 1,
      'source' => [
        'plugin' => 'embedded_data',
        'ids' => ['filename' => ['type' => 'string']],
        'data_rows' => $migration['data_rows'],
        'constants' => [
          'source_base_path' => $this->sourceDir . '/files/',
          'destination_base_uri' => 'public://migrate_default_content_files/',
        ],
      ],
      'process' => [
        'filename' => [
          'plugin' => 'get',
          'source' => 'filename',
        ],
        'destination_full_uri' => [
          'plugin' => 'concat',
          'source' => [
            'constants/destination_base_uri',
            'filename',
          ],
        ],
        'source_full_path' => [
          'plugin' => 'concat',
          'source' => [
            'constants/source_base_path',
            'filename',
          ],
        ],
        'uri' => [
          'plugin' => 'file_copy',
          'source' => [
            '@source_full_path',
            '@destination_full_uri',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity:file',
      ],
    ];
  }

  protected function generateMigrationPlugin(SourcePluginInterface $migration) {
    $migration_plugin = [
      'id' => $migration->getKey(),
      'migration_tags' => ['migrate_default_content'],
      'label' => $migration->getId(),
      'class' => '\Drupal\migrate\Plugin\Migration',
      'status' => 1,
      'destination' => [
        'plugin' => 'entity:' . $migration->getEntityType(),
        'translations' => FALSE,
      ],
    ];

    $migration_plugin['source'] = $migration->getSourceMigrationDefinition();

    // Get bundle info.
    $bundle_key = $this->entityTypeManager->getDefinition($migration->getEntityType())->getKey('bundle');

    if ($bundle_key) {
      $migration_plugin['process'][$bundle_key] = [
        'plugin' => 'default_value',
        'default_value' => $migration->getBundle(),
      ];
    }
    $header = $migration->getHeader();
    foreach ($header as $field) {
      // Headers can have spaces or line endings as is the case for the last
      // field.
      $field = trim($field);
      // This is the field that references the origin node of a translation,
      // it is used to detect that the migration is a translation.
      if ($field == 'translation_origin') {

        // Define the id type of the origin migration.
        $id_type = $this->entityTypeManager->getDefinition($migration->getEntityType())->getKey('id');
        // Define the origin migration.
        $migration_plugin['process'][$id_type] = [
          [
            'plugin' => 'migration_lookup',
            'source' => $field,
            'migration' => $migration->getId(),
          ],
        ];

        // Define the langcode of the translation.
        $migration_plugin['process']['langcode'] = 'constants/langcode';
        // The source will always be the default language of the site.
        $migration_plugin['process']['content_translation_source'] = 'constants/source_langcode';
        // Define the content as translatable.
        $migration_plugin['destination']['translations'] = TRUE;
        // Define the dependency to the origin migration.
        $migration_plugin['migration_dependencies']['required'][] = $migration->getId();

      }
      else {
        // Extra config can be specified like field_name:bundle.
        $extra = explode(':', $field);
        // Handle CSV header fieldSubfield e.g. "bodyFormat".
        $components = preg_split('/([A-Z])/', array_shift($extra), -1, PREG_SPLIT_DELIM_CAPTURE);
        $field = array_shift($components);
        $subfield = strtolower(implode($components));

        $dest_field = empty($subfield) ? $field : $field . '/' . $subfield;

        // It needs to be a normalized process.
        // See: https://api.drupal.org/api/drupal/core!modules!migrate!src!Plugin!Migration.php/function/Migration%3A%3AgetProcessNormalized/
        $migration_plugin['process'][$dest_field] = [
          [
            'plugin' => 'get',
            'source' => $field . ucfirst($subfield) . (empty($extra) ? '' : ':' . implode(':', $extra)),
          ],

        ];

        // If this an entity_reference field and a migrate_default_content
        // migration exists add it automatically.
        /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_definition */
        $field_definition = $this->entityFieldManager->getFieldDefinitions($migration->getEntityType(), $migration->getBundle())[$field];

        // Handle special field types.
        if ($field_definition) {
          $multiple = $field_definition->getFieldStorageDefinition()->getCardinality() !== 1;
          switch ($this->fieldType($field_definition)) {
            case 'entity_reference':
              $settings = $field_definition->getItemDefinition()->getSettings();
              $target_entity_type = $settings['target_type'];

              // In the case of an entity reference the extra configuration is
              // the bundles it should reference.
              if (isset($settings['handler_settings']['target_bundles'])) {
                $extra = array_merge($extra, $settings['handler_settings']['target_bundles']);
              }
              if (!empty($extra)) {
                $target_ids = [];
                // Add all possible referenceable bundles.
                foreach ($extra as $target_bundle) {
                  $target_ids[] = 'mdc_' . $target_entity_type . '_' . $target_bundle;
                }
                $this->addTargetMigration(
                  $migration_plugin,
                  $dest_field,
                  $target_ids,
                  $target_entity_type,
                  $multiple,
                  $field_definition->getType()
                );
              }
              // Assume is an entity with only one bundle. e.g. user.
              // TODO: do not assume. it might be because all bundles are
              // allowed.
              else {
                $target_id = 'mdc_' . $target_entity_type . '_' . $target_entity_type;
                $this->addTargetMigration(
                  $migration_plugin,
                  $dest_field,
                  [$target_id],
                  $target_entity_type,
                  $multiple,
                  $field_definition->getType()
                );
              }

              break;

            case 'file':
              $target_id = 'mdc_file';
              $this->addTargetMigration(
                $migration_plugin,
                $dest_field,
                [$target_id],
                'file',
                $multiple,
                $field_definition->getType()
              );

              break;

            case 'password':
              $migration_plugin['process'][$dest_field][] = [
                'plugin' => 'password_hash',
              ];
              break;
          }
        }
      }
    }

    // For custom file migrations set an uri field that
    // copies the file as needed.
    if ($migration->getEntityType() === 'file') {
      $migration_plugin['process']['uri'] = [
        [
          'plugin' => 'file_copy',
          'source' => [
            '@source_full_path',
            '@destination_full_uri',
          ],
        ],
      ];
    }

    // Look up for an override yml file.
    $override_file_path = $this->sourceDir . '/overrides/' . $migration->getEntityType() . '.' . $migration->getBundle() . '.yml';
    if (file_exists($override_file_path)) {
      $yaml = new Parser();
      $override = $yaml->parse(file_get_contents($override_file_path));
      $migration_plugin = array_merge_recursive($migration_plugin, $override);
    }
    return $migration_plugin;
  }

  /**
   * Add a migration to the process pipeline with its dependency.
   *
   * @param array $migration_plugin
   *   The migration plugin.
   * @param $dest_field
   *   The destination field name.
   * @param $target_ids
   *   The possible target types to reference.
   * @param $target_entity_type
   *   The target entity type to reference.
   * @param bool $multiple
   *   Whether the reference is multiple or not.
   * @param string $field_type
   *   The field type to be referenced.
   */
  protected function addTargetMigration(array &$migration_plugin, $dest_field, $target_ids, $target_entity_type, $multiple, $field_type = 'entity_reference') {
    $migrations = $this->getMigrations();
    $target_ids = array_intersect(array_keys($migrations), $target_ids);
    if (!empty($target_ids)) {
      $dest_subfield = explode('/', $dest_field);
      if (isset($dest_subfield[1]) && $dest_subfield[1] != 'target_id' && $dest_subfield[1] != 'target_revision_id') {
        return $migration_plugin;
      }
      if ($multiple) {
        $target_key = $this->targetMigrationKey($target_ids);

        $process = [
          'plugin' => 'iterator',
          'process' => [
            'target_id' => [
              'plugin' => 'migration_lookup',
              'migration' => $target_ids,
              'source' => $target_key,
              'no_stub' => TRUE,
            ],
          ],
        ];

        // Add the rest of subproperties for this field.
        $definition = \Drupal::service('plugin.manager.field.field_type')->getDefinition($field_type);
        $field_definition = BaseFieldDefinition::create($field_type);
        $schema = call_user_func([$definition['class'], 'schema'], $field_definition);
        foreach (array_keys($schema['columns']) as $subproperty) {
          if ($subproperty != 'target_id') {
            $process['process'][$subproperty] = $subproperty;
          }
          // Add only for entity_reference revisions.
          if ($subproperty == 'target_revision_id') {
            $process['process']['target_revision_id'] = [
              'plugin' => 'content_id_to_revision_reference',
              'target_entity_type' => $target_entity_type,
              'source' => '@target_id',
              'lookup_id' => TRUE,
            ];
          }
        }

        $migration_plugin['process'][$dest_field][] = $process;
      }
      else {
        // We don't need a source because only the first process plugin in
        // the pipeline should have it and we already have the explode plugin.
        $migration_plugin['process'][$dest_field][] = [
          'plugin' => 'migration_lookup',
          'migration' => $target_ids,
          'no_stub' => TRUE,
        ];
        // Add only for entity_reference revisions.
        if ($field_type === 'entity_reference_revisions') {
          $migration_plugin['process'][$dest_field][] = [
            'plugin' => 'content_id_to_revision_reference',
            'target_entity_type' => $target_entity_type,
            'lookup_id' => FALSE,
          ];
        }
      }

      // Avoid dependencies on itself.
      foreach ($target_ids as $target_id) {
        if ($target_id != $migration_plugin['id']) {
          $migration_plugin['migration_dependencies']['required'][] = $target_id;
        }
      }
    }
  }

  /**
   * Provides the generic field type depending on the field item class hierarchy.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition to extract the info from.
   *
   * @return string
   *   The generic field type.
   */
  function fieldType(FieldDefinitionInterface $field_definition) {
    $field_type = \Drupal::service('plugin.manager.field.field_type')->getDefinition($field_definition->getType());
    if (is_a($field_type['class'], FileItem::class, TRUE)) {
      $type = 'file';
    }
    elseif (is_a($field_type['class'], EntityReferenceItem::class, TRUE)) {
      $type = 'entity_reference';
    }
    else {
      $type = $field_definition->getType();
    }
    return $type;
  }

  /**
   * Helper function to get the target migration key.
   *
   * @param array $target_ids
   *   An array containing the different target_ids.
   *
   * @return string
   *   The target migration key.
   */
  protected function targetMigrationKey(array $target_ids) {
    // Assume all the target migrations are configured the same with the same
    // first field.
    $target_id = reset($target_ids);
    if ($target_id === 'mdc_file') {
      $target_key = 'filename';
    } else {
      $migrations = $this->getMigrations();
      $target_key = $migrations[$target_id]->getKey();
    }
    return $target_key;
  }

}
