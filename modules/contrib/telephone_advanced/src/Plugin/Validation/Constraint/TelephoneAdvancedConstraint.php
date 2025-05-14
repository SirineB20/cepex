<?php

namespace Drupal\telephone_advanced\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a telephone number is valid.
 *
 * @Constraint(
 *   id = "TelephoneAdvanced",
 *   label = @Translation("Telephone advanced", context = "Validation")
 * )
 */
class TelephoneAdvancedConstraint extends Constraint {

  /**
   * Message shown when the telephone number isn't valid.
   *
   * @var string
   */
  public $notValidMessage = "@label isn't a valid telephone numer.";

  /**
   * Message shown when the telephone number country doesn't match the required country.
   *
   * @var string
   */
  public $countryNotAllowedSingularMessage = "Only telephone numbers of @country are allowed for @label.";

  /**
   * Message shown when the telephone number country isn't present in the list of countries.
   *
   * @var string
   */
  public $countryNotAllowedPluralMessage = "Only telephone numbers from one of these countries are allowed for @label: @countries.";

  /**
   * Message shown when the telephone number type doesn't match the required type.
   *
   * @var string
   */
  public $typeNotAllowedSingularMessage = "Only @type telephone numbers are allowed for @label.";

  /**
   * Message shown when the telephone number type isn't present in the list of types.
   *
   * @var string
   */
  public $typeNotAllowedPluralMessage = "Only telephone numbers from one of these types are allowed for @label: @types.";

}
