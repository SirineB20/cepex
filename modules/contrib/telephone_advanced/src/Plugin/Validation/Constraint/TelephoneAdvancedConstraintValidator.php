<?php

namespace Drupal\telephone_advanced\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\telephone_advanced\FieldSettings;
use Drupal\telephone_advanced\TelephoneTypes;
use Drupal\telephone_advanced\TelephoneValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the telephone advanced constraint.
 */
class TelephoneAdvancedConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The telephone number validator.
   *
   * @var \Drupal\telephone_advanced\TelephoneValidatorInterface
   */
  protected $telephoneValidator;

  /**
   * The country manager.
   *
   * @var \Drupal\Core\Locale\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\telephone_advanced\TelephoneValidatorInterface $number_validator
   *   The telephone number validator.
   * @param \Drupal\Core\Locale\CountryManagerInterface $country_manager
   *   The country manager.
   */
  public function __construct(TelephoneValidatorInterface $number_validator, CountryManagerInterface $country_manager) {
    $this->telephoneValidator = $number_validator;
    $this->countryManager = $country_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('telephone_advanced.telephone_validator'),
      $container->get('country_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    if ($item->isEmpty()) {
      return;
    }

    // Check if the validation has been enabled.
    $field_definition = $item->getFieldDefinition();

    if (!FieldSettings::isEnabled($field_definition)) {
      return;
    }

    // Get the field label and settings.
    $label = $item->getFieldDefinition()->getLabel();
    $default_country = FieldSettings::getDefaultCountry($field_definition);
    $allowed_countries = FieldSettings::getAllowedCountries($field_definition);
    $allowed_types = FieldSettings::getAllowedTypes($field_definition);

    // Validate the telephone number.
    if (!$this->telephoneValidator->isValid($item->value, $default_country)) {
      /** @var \Drupal\telephone_advanced\Plugin\Validation\Constraint\TelephoneAdvancedConstraint $constraint */
      $this->context->addViolation($constraint->notValidMessage, [
        '@label' => $label,
      ]);

      return;
    }

    // Validate the country.
    if ($allowed_countries && !$this->telephoneValidator->isFromCountry($item->value, $allowed_countries, $default_country)) {
      /** @var \Drupal\Core\Locale\CountryManagerInterface $country_manager */
      $countries = array_intersect_key(
        $this->countryManager->getList(),
        array_flip($allowed_countries)
      );
      $countries = implode(', ', $countries);

      if (count($allowed_countries) === 1) {
        /** @var \Drupal\telephone_advanced\Plugin\Validation\Constraint\TelephoneAdvancedConstraint $constraint */
        $this->context->addViolation($constraint->countryNotAllowedSingularMessage, [
          '@label' => $label,
          '@country' => $countries,
        ]);
      }
      else {
        /** @var \Drupal\telephone_advanced\Plugin\Validation\Constraint\TelephoneAdvancedConstraint $constraint */
        $this->context->addViolation($constraint->countryNotAllowedPluralMessage, [
          '@label' => $label,
          '@countries' => $countries,
        ]);
      }
    }

    // Validate the type.
    if ($allowed_types && !$this->telephoneValidator->isOfType($item->value, $allowed_types, FALSE, $default_country)) {
      if (count($allowed_types) === 1) {
        /** @var \Drupal\telephone_advanced\Plugin\Validation\Constraint\TelephoneAdvancedConstraint $constraint */
        $this->context->addViolation($constraint->typeNotAllowedSingularMessage, [
          '@label' => $label,
          '@type' => mb_strtolower(TelephoneTypes::getLabel(reset($allowed_types))),
        ]);
      }
      else {
        $types = array_intersect_key(TelephoneTypes::getLabels(), array_flip($allowed_types));
        $types = mb_strtolower(implode(', ', $types));

        /** @var \Drupal\telephone_advanced\Plugin\Validation\Constraint\TelephoneAdvancedConstraint $constraint */
        $this->context->addViolation($constraint->typeNotAllowedPluralMessage, [
          '@label' => $label,
          '@types' => $types,
        ]);
      }
    }
  }

}
