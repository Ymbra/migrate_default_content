<?php

namespace Drupal\migrate_default_content\Event;

use Drupal\migrate_plus\Event\MigrateEvents;
use Drupal\migrate_plus\Event\MigratePrepareRowEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event susbscriber to manipulate data before importing it as content.
 */
class MigrateEvent implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  static public function getSubscribedEvents() {
    $events[MigrateEvents::PREPARE_ROW][] = array('onPrepareRow', 0);
    return $events;
  }

  /**
   * React to a new row.
   *
   * @param \Drupal\migrate_plus\Event\MigratePrepareRowEvent $event
   *   The prepare-row event.
   */
  public function onPrepareRow(MigratePrepareRowEvent $event) {
    $migration = $event->getMigration();
    if ($migration->migration_group === 'migrate_default_content') {
      $row = $event->getRow();

      // Handle values that are JSON.
      foreach ($row->getSource() as $id => $value) {
        $property = $row->getSourceProperty($id);
        if (is_string($property)  && (substr($property, 0, 1) === '{' || substr($property, 0, 1) === '[')) {
          $row->setSourceProperty($id, json_decode(stripslashes($property), TRUE));
        }
      }

      // Get the destination entity type.
      $destination_plugin = $migration->getDestinationConfiguration()['plugin'];
      $entity_type = explode(':', $destination_plugin)[1];

      $bundle_key = \Drupal::service('entity_type.manager')->getDefinition($entity_type)->getKey('bundle');

      // Get the bundle.
      if ($bundle_key) {
        foreach ($migration->getProcess()[$bundle_key] as $plugin) {
          if ($plugin['plugin'] === 'default_value') {
            $bundle = $plugin['default_value'];
          }
        }
      }
      else {
        $bundle = $entity_type;
      }

      $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $bundle);
      foreach ($fields as $field_name => $definition) {
        // Case for password fields.
        if ($definition->getType() === 'password') {
          $row->setSourceProperty($field_name, \Drupal::getContainer()->get('password')->hash(
            $row->getSourceProperty($field_name)
          ));
        }
      }
    }
  }

}
