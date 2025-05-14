<?php

namespace Drupal\telephone_advanced;

use libphonenumber\PhoneNumberType;

/**
 * Provides the telephone number types.
 */
class TelephoneTypes {

  /**
   * Get all telephone number type IDs.
   *
   * @return string[]
   *   The type IDs.
   */
  public static function getIds() {
    $ids = PhoneNumberType::values();

    unset($ids[PhoneNumberType::UNKNOWN]);

    return $ids;
  }

  /**
   * Get all telephone number type labels.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   The type labels.
   */
  public static function getLabels() {
    return [
      PhoneNumberType::FIXED_LINE => t('Fixed line'),
      PhoneNumberType::MOBILE => t('Mobile'),
      PhoneNumberType::PAGER => t('Pager'),
      PhoneNumberType::VOIP => t('VOIP'),
      PhoneNumberType::PERSONAL_NUMBER => t('Personal number'),
      PhoneNumberType::UAN => t('UAN'),
      PhoneNumberType::TOLL_FREE => t('Toll free'),
      PhoneNumberType::STANDARD_RATE => t('Standard rate'),
      PhoneNumberType::SHARED_COST => t('Shared cost'),
      PhoneNumberType::PREMIUM_RATE => t('Premium rate'),
      PhoneNumberType::VOICEMAIL => t('Voicemail'),
      PhoneNumberType::SHORT_CODE => t('Short code'),
      PhoneNumberType::EMERGENCY => t('Emergency'),
    ];
  }

  /**
   * Get a telephone number type label.
   *
   * @param int $type
   *   The type.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   The type label or NULL if unknown.
   */
  public static function getLabel($type) {
    $labels = static::getLabels();

    return $labels[$type] ?? NULL;
  }

}
