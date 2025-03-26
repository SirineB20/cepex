<?php

namespace Drupal\entity_model\Entity\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * A trait containing utility methods for translating entities.
 */
trait EntityTranslatorTrait {

  /**
   * Get the translated versions of an array of entities.
   *
   * @param array $entities
   *   The array of entities that should be translated.
   * @param string|null $langcode
   *   The langcode the entities should be translated into.
   *   If this is NULL, the current content language will be used.
   * @param bool $strict
   *   If this is TRUE and the entity is not translated into the given langcode,
   *   NULL will be returned. Else, the default langcode will be returned.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of translated entities.
   */
  protected function translateEntities(array $entities = [], ?string $langcode = NULL, bool $strict = TRUE): array {
    $translated = [];
    foreach ($entities as $key => $entity) {
      $translated[$key] = $this->translateEntity($entity, $langcode, $strict);
    }

    return array_filter($translated);
  }

  /**
   * Get the translated version of an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   The entity that should be translated.
   * @param string|null $langcode
   *   The langcode the entities should be translated into.
   *   If this is NULL, the current content language will be used.
   * @param bool $strict
   *   If this is TRUE and the entity is not translated into the given langcode,
   *   NULL will be returned. Else, the default langcode will be returned.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The translated entity.
   */
  protected function translateEntity(?EntityInterface $entity, ?string $langcode = NULL, bool $strict = TRUE): ?EntityInterface {
    if (!$langcode) {
      $langcode = \Drupal::languageManager()
        ->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
        ->getId();
    }

    if (
      !$entity
      || !$entity instanceof TranslatableInterface
      || $entity->language()->getId() === $langcode
      || $entity->isTranslatable() === false
    ) {
      return $entity;
    }

    if (!$entity->hasTranslation($langcode)) {
      return $strict ? NULL : $entity;
    }

    return $entity->getTranslation($langcode);
  }

}
