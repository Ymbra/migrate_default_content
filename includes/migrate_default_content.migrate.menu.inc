<?php
/**
 * @file
 * Create default items to populate menus.
 */

class defaultMenu extends defaultBaseMigration {

  public function __construct(array $arguments) {
    $this->arguments = $arguments;
    parent::__construct($arguments);

    $this->description = t('Import @menu_name menu items', array('@menu_name' => $this->migrationData['menu_name']));
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
