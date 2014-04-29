<?php
/**
 * @file
 * Populate specific user content.
 */

class defaultUser extends defaultEntity {

  // Set up Roles.
  public function prepareRow($current_row) {
    if (!empty($current_row->roles)) {
      $roles = explode("|", $current_row->roles);
      $role_ids = array();
      foreach ($roles as $rol) {
        $role_loaded = user_role_load_by_name($rol);
        $role_ids[$role_loaded->rid] = $role_loaded->rid;
      }
      $current_row->roles = $role_ids;
    }
  }

}
