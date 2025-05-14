<?php

namespace Drupal\telephone_advanced;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;

/**
 * Provides the telephone formatter.
 */
class TelephoneFormatter implements TelephoneFormatterInterface {

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
  public function format($number, $format, $default_country = NULL) {
    if (!$number instanceof PhoneNumber) {
      $number = $this->telephoneParser->parse($number, $default_country);
    }

    return PhoneNumberUtil::getInstance()->format($number, $format);
  }

}
