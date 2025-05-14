<?php

namespace Drupal\telephone_advanced;

use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Provides methods to get our field settings.
 */
class FieldSettings {

  /**
   * Check if our advanced features have been enabled.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return bool
   *   TRUE if our advanced features have been enabled.
   */
  public static function isEnabled(FieldDefinitionInterface $field_definition) {
    return (bool) static::getSetting($field_definition, 'enabled', FALSE);
  }

  /**
   * Get the default country.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return string|null
   *   The default country or NULL if not applicable.
   */
  public static function getDefaultCountry(FieldDefinitionInterface $field_definition) {
    return static::getSetting($field_definition, 'default_country');
  }

  /**
   * Get the allowed countries.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return array
   *   The allowed country codes or an empty array if all countries are allowed.
   */
  public static function getAllowedCountries(FieldDefinitionInterface $field_definition) {
    return (array) static::getSetting($field_definition, 'allowed_countries', []);
  }

  /**
   * Get the allowed telephone number types.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return array
   *   The allowed telephone number types or an empty array if all types are allowed.
   */
  public static function getAllowedTypes(FieldDefinitionInterface $field_definition) {
    return (array) static::getSetting($field_definition, 'allowed_types', []);
  }

  /**
   * Get the storage format.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return int|null
   *   The storage format or NULL if not applicable.
   */
  public static function getStorageFormat(FieldDefinitionInterface $field_definition) {
    return static::getSetting($field_definition, 'storage_format');
  }

  /**
   * Get one of our settings from the field definition.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param string $name
   *   The setting name.
   * @param mixed $default
   *   The default value if missing.
   *
   * @return mixed
   *   The setting value or default if missing.
   */
  protected static function getSetting(FieldDefinitionInterface $field_definition, $name, $default = NULL) {
    if ($field_definition instanceof ThirdPartySettingsInterface) {
      return $field_definition->getThirdPartySetting('telephone_advanced', $name, $default);
    }

    if (!$field_definition instanceof BaseFieldDefinition) {
      return $default;
    }

    $settings = $field_definition->getSetting('third_party_settings')['telephone_advanced'] ?? [];

    if (array_key_exists($name, $settings)) {
      return $settings[$name];
    }

    return $default;
  }

}
