<?php

namespace Drupal\telephone_advanced;

/**
 * Interface for the telephone formatter service.
 */
interface TelephoneFormatterInterface {

  /**
   * Format a telephone number.
   *
   * @param string $number
   *   The telephone number.
   * @param int $format
   *   The desired format.
   * @param string|null $default_country
   *   The default country code. Can be NULL when $number is a PhoneNumber object
   *   or if it's guaranteed to start with a country calling code.
   *
   * @return string
   *   The formatted telephone number.
   */
  public function format($number, $format, $default_country = NULL);

}
