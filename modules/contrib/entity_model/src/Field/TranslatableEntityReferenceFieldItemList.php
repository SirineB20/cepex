<?php

namespace Drupal\entity_model\Field;

use Drupal\Core\Field\EntityReferenceFieldItemList;

/**
 * Defines a field item list class for translatable entity reference fields.
 */
class TranslatableEntityReferenceFieldItemList extends EntityReferenceFieldItemList {

  use TranslatableEntityReferenceFieldItemListTrait;

}
