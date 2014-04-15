<?php
/**
 * @file
 * Populate content for entities.
 */

class defaultContentMenu extends defaultContentBaseMigration {

  public function __construct(array $arguments) {
    $this->arguments = $arguments;
    parent::__construct();

    $this->description = t('Import @type - @bundle from file.', array('@type' => $this->migrationData['entity_type'], '@bundle' => $this->migrationData['bundle']));
    $this->addDefaultMappings();

    // Create a map object for tracking the relationships between source rows.
    $this->map = new MigrateSQLMap($this->machineName,
      array(
        'id' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ),
      ),
      MigrateDestinationMenuLinks::getKeySchema());

    // Create a MigrateSource object.
    $this->source = $this->getMigrationSource();
    $this->destination = new MigrateDestinationMenuLinks();

    // Default mappings.
    // Parent menu.
    $this->addFieldMapping('menu_name')->defaultValue($this->migrationData['menu_name']);
    $this->addFieldMapping('plid', 'parent')->sourceMigration($this->getMachineName());

  }

  /**
   * Implements prepareRow().
   *
   * - Add the menu link weight.
   * - Set module to menu to support menu_firstchild.
   */
  public function prepareRow($row) {
    static $weight;
    if (empty($weight)) {
      $weight = 0;
    }
    $row->weight = ++$weight;
    $row->module = 'menu';
  }

  /**
   * Implements MigrateDestination::prepare().
   *
   * Set up the node.
   */
  public function prepare($menu_link, $row) {
    if (!empty($menu_link->node)) {
      $menu_link->link_path = 'node/' . $menu_link->node['destid1'];
      unset($menu_link->node);
    }
  }
}
