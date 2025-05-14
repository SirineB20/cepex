<?php

namespace Drupal\telephone_advanced\Plugin\Field\FieldType;

use Drupal\telephone\Plugin\Field\FieldType\TelephoneItem as TelephoneTelephoneItem;
use Drupal\telephone_advanced\FieldSettings;
use Drupal\telephone_advanced\TelephoneFormatterInterface;

/**
 * Overrides the default telephone field class.
 */
class TelephoneItem extends TelephoneTelephoneItem {

  /**
   * The telephone formatter.
   *
   * @var \Drupal\telephone_advanced\TelephoneFormatterInterface|null
   */
  protected $telephoneFormatter;

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    $field_definition = $this->getFieldDefinition();

    if (!FieldSettings::isEnabled($field_definition)) {
      return;
    }

    $storage_format = FieldSettings::getStorageFormat($field_definition);

    if ($storage_format === NULL) {
      return;
    }

    $value = $this->getTelephoneFormatter()->format(
      $this->getValue()['value'],
      $storage_format,
      FieldSettings::getDefaultCountry($field_definition)
    );

    $this->set('value', $value, FALSE);
  }

  /**
   * Set the telephone formatter.
   *
   * @param \Drupal\telephone_advanced\TelephoneFormatterInterface $telephone_formatter
   *   The telephone formatter.
   */
  public function setTelephoneFormatter(TelephoneFormatterInterface $telephone_formatter) {
    $this->telephoneFormatter = $telephone_formatter;
  }

  /**
   * Get the telephone formatter.
   *
   * @return \Drupal\telephone_advanced\TelephoneFormatterInterface
   *   The telephone formatter.
   */
  protected function getTelephoneFormatter() {
    if (!isset($this->telephoneFormatter)) {
      /** @var \Drupal\telephone_advanced\TelephoneFormatterInterface $formatter */
      $formatter = \Drupal::service('telephone_advanced.telephone_formatter');
      $this->telephoneFormatter = $formatter;
    }

    return $this->telephoneFormatter;
  }

}
