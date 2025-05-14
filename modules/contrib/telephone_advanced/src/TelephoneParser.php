<?php

namespace Drupal\telephone_advanced;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

/**
 * Provides the telephone parser.
 */
class TelephoneParser implements TelephoneParserInterface {

  /**
   * The cached results.
   *
   * @var array
   */
  protected $results = [];

  /**
   * {@inheritdoc}
   */
  public function parse($number, $default_country = NULL) {
    $key = $default_country . '|' . $number;

    if (!isset($this->results[$key])) {
      $this->results[$key] = PhoneNumberUtil::getInstance()
        ->parse($number, $default_country);
    }

    return $this->results[$key];
  }

  /**
   * {@inheritdoc}
   */
  public function getCountry($number, $default_country = NULL) {
    if (!$number instanceof PhoneNumber) {
      try {
        $number = $this->parse($number, $default_country);
      }
      catch (NumberParseException $ex) {
        return NULL;
      }
    }

    return PhoneNumberUtil::getInstance()->getRegionCodeForNumber($number);
  }

  /**
   * {@inheritdoc}
   */
  public function getType($number, $default_country = NULL) {
    if (!$number instanceof PhoneNumber) {
      try {
        $number = $this->parse($number, $default_country);
      }
      catch (NumberParseException $ex) {
        return NULL;
      }
    }

    $type = PhoneNumberUtil::getInstance()->getNumberType($number);

    if ($type === PhoneNumberType::UNKNOWN) {
      return NULL;
    }

    return $type;
  }

}
