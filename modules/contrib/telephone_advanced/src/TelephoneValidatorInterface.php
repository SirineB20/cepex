<?php

namespace Drupal\telephone_advanced;

/**
 * Interface for the telephone validator service.
 */
interface TelephoneValidatorInterface {

  /**
   * Check if a telephone number is valid.
   *
   * @param string $number
   *   The telephone number.
   * @param string|null $default_country
   *   The default country code, leave NULL if the number is guaranteed
   *   to start with a country calling code.
   *
   * @return bool
   *   TRUE if the telephone number is valid.
   */
  public function isValid($number, $default_country = NULL);

  /**
   * Check if a telephone number is from a country.
   *
   * @param string|\libphonenumber\PhoneNumber $number
   *   The telephone number.
   * @param string|array $countries
   *   The allowed country or countries.
   * @param string|null $default_country
   *   The default country code. Can be NULL when $number is a PhoneNumber object
   *   or if it's guaranteed to start with a country calling code.
   *
   * @return bool
   *   TRUE if the telephone number is from one of the specified countries.
   */
  public function isFromCountry($number, $countries, $default_country = NULL);

  /**
   * Check the telephone number type.
   *
   * @param string|\libphonenumber\PhoneNumber $number
   *   The telephone number.
   * @param int|array $types
   *   The allowed type or types.
   * @param bool $strict
   *   Set to TRUE to enforce a strict type check. If TRUE, PhoneNumberType::FIXED_LINE_OR_MOBILE
   *   will not match PhoneNumberType::FIXED_LINE nor PhoneNumberType::MOBILE.
   * @param string|null $default_country
   *   The default country code. Can be NULL when $number is a PhoneNumber object
   *   or if it's guaranteed to start with a country calling code.
   *
   * @return bool
   *   TRUE if the telephone number is one of the specified types.
   */
  public function isOfType($number, $types, $strict = FALSE, $default_country = NULL);

}
