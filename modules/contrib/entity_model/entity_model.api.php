<?php

use Drupal\my_module\Entity\Model\Node\Page;

/**
 * @file
 * Example implementations for the hooks defined by the Entity Model module.
 */

/**
 * Alter the definitions of the entity model plugins.
 *
 * @param array<string, array{ entity_type: string, bundle: string, class: string, provider: string }> $definitions
 *   The plugin definitions.
 */
function hook_entity_model_model_info_alter(array &$definitions) {
  $definitions['node.page']['class'] = Page::class;
}
