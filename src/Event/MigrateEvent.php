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
    }
  }

}
