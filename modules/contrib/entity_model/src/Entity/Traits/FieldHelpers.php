<?php

namespace Drupal\entity_model\Entity\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\TimestampItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\link\Plugin\Field\FieldType\LinkItem;

/**
 * A trait containing utility methods for extracting field values from entities.
 */
trait FieldHelpers {

  /**
   * Get the DateTime object from a date-like field.
   */
  protected function getDateTime(string $field_name): ?\DateTimeInterface {
    $dateTimes = $this->getDateTimes($field_name);

    return reset($dateTimes) ?: NULL;
  }

  /**
   * Get the DateTime objects from a date-like field.
   *
   * @return \DateTimeInterface[]
   *   An array of DateTime objects.
   */
  protected function getDateTimes(string $field_name): array {
    if (!$this->hasField($field_name) || $this->get($field_name)->isEmpty()) {
      return [];
    }

    $dateTimes = [];

    foreach ($this->get($field_name) as $field) {
      if ($field instanceof TimestampItem) {
        $timestamp = $field->value;
      }

      if ($field instanceof DateTimeItem) {
        if (!$date = $field->date) {
          continue;
        }

        $timestamp = $date->format('U');
      }

      if (!isset($timestamp)) {
        throw new \InvalidArgumentException(
          sprintf('FieldHelpers::getDateTimes cannot deal with %s fields.', $field->getFieldDefinition()->getType())
        );
      }

      $dateTimes[] = \DateTime::createFromFormat('U', $timestamp)
        ->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    }

    return $dateTimes;
  }

  /**
   * Set the value of a date-like field to a DateTime object.
   *
   * @param string $field_name
   *   The name of the field to store the values in.
   * @param \DateTimeInterface $date_time
   *   The DateTime object to store in the field.
   *
   * @return static
   *   The calling object, in order to make this method chainable.
   */
  protected function setDateTime(string $field_name, \DateTimeInterface $date_time) {
    return $this->setDateTimes($field_name, [$date_time]);
  }

  /**
   * Set the value of a date-like field to an array of DateTime objects.
   *
   * @param string $field_name
   *   The name of the field to store the values in.
   * @param \DateTimeInterface[] $date_times
   *   An array of DateTime objects to store in the field.
   *
   * @return static
   *   The calling object, in order to make this method chainable.
   */
  protected function setDateTimes(string $field_name, array $date_times) {
    $fieldDefinition = $this->get($field_name)->getFieldDefinition();
    $fieldType = $fieldDefinition->getType();

    if (in_array($fieldType, ['created', 'changed', 'timestamp'], TRUE)) {
      $storageFormat = 'U';
    }

    if ($fieldType === 'datetime') {
      $datetimeType = $fieldDefinition->getSetting('datetime_type');
      $storageFormat = $datetimeType === DateTimeItem::DATETIME_TYPE_DATE
        ? DateTimeItemInterface::DATE_STORAGE_FORMAT
        : DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
    }

    if (!isset($storageFormat)) {
      throw new \InvalidArgumentException(
        sprintf('FieldHelpers::setDateTime cannot deal with %s fields.', $fieldType)
      );
    }

    return $this->set($field_name, array_map(
      static function (\DateTimeInterface $date_time) use ($storageFormat) {
        return $date_time
          ->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE))
          ->format($storageFormat);
      },
      $date_times
    ));
  }

  /**
   * Get the source field item list of a referenced media entity.
   */
  protected function getMediaSource(string $field_name): ?FieldItemListInterface {
    if (!$media = $this->get($field_name)->entity) {
      return NULL;
    }

    $sourceConfig = $media->getSource()->getConfiguration();
    $sourceFieldName = $sourceConfig['source_field'] ?? NULL;

    return $media->get($sourceFieldName);
  }

  /**
   * Get a structured array with field values from a link field.
   *
   * @return array<int, array{ url: string, text: string, external: bool, target: string }>
   *   The structured link array.
   */
  protected function formatLinks(string $field_name): array {
    $links = [];

    if (!$this->hasField($field_name) || $this->get($field_name)->isEmpty()) {
      return $links;
    }

    foreach ($this->get($field_name) as $value) {
      $links[] = $this->formatLinkItem($value);
    }

    return $links;
  }

  /**
   * Get a structured array with field values from a link field.
   *
   * @return array{ url: string, text: string, external: bool, target: string }
   *   The structured link array.
   */
  protected function formatLink(string $field_name): array {
    $link = [
      'url' => '',
      'text' => '',
      'external' => FALSE,
      'target' => '_self',
    ];

    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $item */
    if (!$this->hasField($field_name) || $this->get($field_name)->isEmpty()) {
      return $link;
    }

    return $this->formatLinkItem(
      $this->get($field_name)->first()
    );
  }

  /**
   * Get a structured array with field values from a link field item.
   *
   * @return array{ url: string, text: string, external: bool, target: string, entity: EntityInterface|null }
   *   The structured link array.
   */
  private function formatLinkItem(LinkItem $item): array {
    $link = [
      'url' => '',
      'text' => '',
      'external' => FALSE,
      'entity' => NULL,
    ];

    $noLinkRoutes = ['<nolink>', '<none>'];

    if ($item->getUrl()->isRouted() && in_array($item->getUrl()->getRouteName(), $noLinkRoutes)) {
      if ($fragment = $item->getUrl()->getOption('fragment')) {
        $url = '#' . $fragment;
      }
      else {
        $url = '';
      }
    }
    elseif ($entity = $this->getReferencedEntityFromLink($item)) {
      $link['entity'] = $entity;
      $url = $entity->toUrl()->toString();
    }
    else {
      $url = $item->getUrl()->toString();
    }

    $link['url'] = $url;
    $link['text'] = $item->title ?? '';
    $link['external'] = $item->isExternal();

    return $link;
  }

  /**
   * Extract the referenced entity from a link field item.
   */
  private function getReferencedEntityFromLink(LinkItem $link): ?EntityInterface {
    $uri = explode(':', $link->uri, 2);
    if ($uri[0] !== 'entity' || count($uri) !== 2) {
      return NULL;
    }

    [$entityTypeId, $entityId] = explode('/', $uri[1], 2);

    $entity = $this->entityTypeManager()
      ->getStorage($entityTypeId)
      ->load($entityId);

    return $this->translateEntity($entity);
  }

}
