<?php

namespace Drupal\entity_model;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\entity_model\Annotation\Model;

/**
 * The entity model plugin manager.
 *
 * @see \Drupal\entity_model\Annotation\Model
 * @see plugin_api
 */
class ModelPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      'Entity',
      $namespaces,
        $module_handler,
      ContentEntityInterface::class,
      Model::class
    );
    $this->alterInfo('entity_model_model_info');
    $this->setCacheBackend($cache_backend, 'entity_model_model_info_plugins');
  }

}
