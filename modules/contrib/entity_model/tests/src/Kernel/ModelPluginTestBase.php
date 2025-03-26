<?php

namespace Drupal\Tests\entity_model\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * A base class for entity model plugin tests.
 */
abstract class ModelPluginTestBase extends KernelTestBase {

  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field',
    'text',
    'system',
    'user',
    'entity_model',
    'entity_model_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(static::$modules);
    $this->createContentType(['type' => 'page']);
  }

}
