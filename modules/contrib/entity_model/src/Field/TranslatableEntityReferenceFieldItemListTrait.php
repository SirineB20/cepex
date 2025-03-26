<?php

namespace Drupal\entity_model\Field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;

/**
 * Defines a trait for translatable entity reference fields.
 *
 * @see https://www.drupal.org/project/drupal/issues/2915972
 *   EntityReferenceFieldItemList::referencedEntities should return the
 *   translated entity.
 */
trait TranslatableEntityReferenceFieldItemListTrait {

  public function __get($propertyName) {
    if ($propertyName === 'translations') {
      return $this->getTranslations();
    }

    if ($propertyName === 'translation') {
      return $this->getTranslation();
    }

    return parent::__get($propertyName);
  }

  /**
   * Get the translation of the referenced entity.
   *
   * @param string|null $langcode
   *   The language code to translate to.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The translated entity or null if no translation is available.
   */
  public function getTranslation(?string $langcode = NULL): ?EntityInterface {
    if ($this->entity instanceof EntityInterface) {
      return \Drupal::service('entity.repository')
        ->getTranslationFromContext($this->entity, $langcode);
    }

    return NULL;
  }

  /**
   * Get the translations of the referenced entities.
   *
   * @param string|null $langcode
   *   The language code to translate to.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The translated entities.
   */
  public function getTranslations(?string $langcode = NULL): array {
    $targetEntities = $this->referencedEntities();

    foreach ($targetEntities as $delta => $targetEntity) {
      if (!$targetEntity instanceof TranslatableInterface) {
        break;
      }

      $targetEntities[$delta] = \Drupal::service('entity.repository')
        ->getTranslationFromContext($targetEntity, $langcode);
    }

    return $targetEntities;
  }

}
