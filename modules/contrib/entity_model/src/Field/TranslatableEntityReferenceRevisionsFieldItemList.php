<?php

namespace Drupal\entity_model\Field;

use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;

/**
 * Defines a field item list class for translatable entity reference revisions fields.
 */
class TranslatableEntityReferenceRevisionsFieldItemList extends EntityReferenceRevisionsFieldItemList {

  use TranslatableEntityReferenceFieldItemListTrait;

}
