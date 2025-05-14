<?php

namespace Drupal\telephone_advanced;

/**
 * Interface for the telephone parser service.
 */
interface TelephoneParserInterface {

  /**
   * Parse a telephone number.
   *
   * @param string $number
   *   The telephone number.
   * @param string|null $default_country
   *   The default country code, leave NULL if the number is guaranteed
   *   to start with a country calling code.
   *
   * @return \libphonenumber\PhoneNumber
   *   The phone number object.
   *
   * @throws \libphonenumber\NumberParseException
   */
  public function parse($number, $default_country = NULL);

  /**
   * Get the country of a telephone number.
   *
   * @param string|\libphonenumber\PhoneNumber $number
   *   The telephone number.
   * @param string|null $default_country
   *   The default country code. Can be NULL when $number is a PhoneNumber object
   *   or if it's guaranteed to start with a country calling code.
   *
   * @return string|null
   *   The country of the telephone number or NULL if unknown.
   */
  public function getCountry($number, $default_country = NULL);

  /**
   * Get the telephone number type.
   *
   * @param string|\libphonenumber\PhoneNumber $number
   *   The telephone number.
   * @param string|null $default_country
   *   The default country code. Can be NULL when $number is a PhoneNumber object
   *   or if it's guaranteed to start with a country calling code.
   *
   * @return string|null
   *   The telephone number type or NULL if unknown.
   */
  public function getType($number, $default_country = NULL);

}
