<?php

namespace Drupal\telephone_advanced;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberType;

/**
 * Provides the telephone validator.
 */
class TelephoneValidator implements TelephoneValidatorInterface {

  /**
   * The telephone parser.
   *
   * @var \Drupal\telephone_advanced\TelephoneParserInterface
   */
  protected $telephoneParser;

  /**
   * Class constructor.
   *
   * @param \Drupal\telephone_advanced\TelephoneParserInterface $telephone_parser
   *   The telephone parser.
   */
  public function __construct(TelephoneParserInterface $telephone_parser) {
    $this->telephoneParser = $telephone_parser;
  }

  /**
   * {@inheritdoc}
   */
  public function isValid($number, $default_country = NULL) {
    try {
      $this->telephoneParser->parse($number, $default_country);
    }
    catch (NumberParseException $ex) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isFromCountry($number, $countries, $default_country = NULL) {
    if (!$country = $this->telephoneParser->getCountry($number, $default_country)) {
      return FALSE;
    }

    return in_array($country, (array) $countries, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function isOfType($number, $types, $strict = FALSE, $default_country = NULL) {
    $type = $this->telephoneParser->getType($number, $default_country);

    if ($type === NULL) {
      return FALSE;
    }

    $types = (array) $types;

    if (!$strict) {
      if (in_array(PhoneNumberType::FIXED_LINE, $types, FALSE)) {
        $types[] = PhoneNumberType::FIXED_LINE_OR_MOBILE;
      }

      if (in_array(PhoneNumberType::MOBILE, $types, FALSE)) {
        $types[] = PhoneNumberType::FIXED_LINE_OR_MOBILE;
      }
    }

    return in_array($type, $types, FALSE);
  }

}
