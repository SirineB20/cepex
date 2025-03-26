<?php

namespace Drupal\entity_model\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an entity model annotation object.
 *
 * @Annotation
 */
class Model extends Plugin {

  /**
   * The entity type ID.
   *
   * @var string
   */
  public $entity_type;

  /**
   * The entity bundle.
   *
   * @var string
   */
  public $bundle;

  /**
   * {@inheritdoc}
   */
  public function getId() {
    if (isset($this->definition['bundle'])) {
      return implode('.', [
        $this->definition['entity_type'],
        $this->definition['bundle'],
      ]);
    }

    if (isset($this->definition['entity_type'])) {
      return $this->definition['entity_type'];
    }

    return parent::getId();
  }

}
