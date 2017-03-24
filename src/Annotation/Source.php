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
   * An array of valid extensions for this source.
   *
   * @var array
   */
  public $extensions;
}
