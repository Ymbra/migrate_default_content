<?php
namespace Drupal\migrate_default_content\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Source annotation object.
 *
 * @Annotation
 */
class Source extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The valid extensions for this source.
   *
   * @var string
   */
  public $extension;
}
