<?php

namespace Drupal\entity_model\Commands;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\entity_model\ModelPluginManager;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for the Entity Model module.
 */
class EntityModelCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity model plugin manager.
   *
   * @var \Drupal\entity_model\ModelPluginManager
   */
  protected $pluginManager;

  /**
   * Constructs a new EntityModelCommands instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\entity_model\ModelPluginManager $plugin_manager
   *   The entity model plugin manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ModelPluginManager $plugin_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->pluginManager = $plugin_manager;
  }

  /**
   * List all bundles and their mapping.
   *
   * @command entity_model:list
   * @aliases model-list,eml
   * @option filter-mapped-status Only list bundle classes with the given mapped status (mapped or unmapped).
   */
  public function listModels(
    array $options = ['filter-mapped-status' => 'both']
  ) {
    $this->validateOption(
      $options,
      'filter-mapped-status',
      ['mapped', 'unmapped', 'both']
    );

    $showMapped = in_array(
        $options['filter-mapped-status'],
        ['mapped', 'both'],
        TRUE
    );
    $showUnmapped = in_array(
        $options['filter-mapped-status'],
        ['unmapped', 'both'],
        TRUE
    );

    foreach ($this->entityTypeManager->getDefinitions() as $entityType) {
      if (!$bundleEntityType = $entityType->getBundleEntityType()) {
        continue;
      }

      if (!$entityType instanceof ContentEntityTypeInterface) {
        continue;
      }

      $bundles = $this->entityTypeManager
        ->getStorage($bundleEntityType)
        ->getQuery()
        ->accessCheck(FALSE)
        ->execute();

      foreach ($bundles as $bundle) {
        $id = implode('.', [$entityType->id(), $bundle]);
        $isMapped = $this->pluginManager->hasDefinition($id);

        $message = NULL;
        if ($showMapped && $isMapped) {
          $message = $this->t('Model "@model" is mapped against "@mapping".', [
            '@model' => $id,
            '@mapping' => $this->pluginManager->getDefinition($id)['class'],
          ]);
        }
        elseif ($showUnmapped && !$isMapped) {
          $message = $this->t('Model "@model" is not mapped.', [
            '@model' => $id,
          ]);
        }

        if ($message !== NULL) {
          $this->io()->text($message);
        }
      }
    }
  }

  /**
   * Validates the given option.
   *
   * @param array $options
   *   The options array.
   * @param string $name
   *   The name of the option to validate.
   * @param array $allowedValues
   *   The allowed values for the option.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the option is not valid.
   */
  private function validateOption(
    array $options,
    string $name,
    array $allowedValues
  ): void {
    if (!in_array($options[$name], $allowedValues, TRUE)) {
      throw new \InvalidArgumentException("Invalid --{$name} option. Possible values are '" . implode("', '", $allowedValues) . "'.");
    }
  }

}
