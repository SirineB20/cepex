Telephone Advanced
==================

Adds validation and formatting to Drupal core's telephone field.


## Requirements

* [giggsey/libphonenumber-for-php](https://github.com/giggsey/libphonenumber-for-php)


## Installation

Start by downloading the latest release and its dependencies using `composer require drupal/telephone_advanced`.
You can also download it manually, but please make sure all requirements are fulfilled before continuing.

Next, simply enable the module as described in the [the module installation guide](https://www.drupal.org/docs/extending-drupal/installing-modules).


## Configuration

Add a telephone field and configure the validation and formatting on the field instance settings.


## Custom forms

You can validate and format the value of a "tel" element as well. Just add the `#telephone_advanced`
property to the `tel` element in your form (all settings are optional):

```php
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;

$form['phone'] = [
  '#type' => 'tel',
  '#title' => t('Phone'),
  '#telephone_advanced' => [
    'default_country' => 'BE', // Default country (highly recommended)
    'allowed_countries' => ['BE', 'NL', 'FR', 'DE'], // Allowed countries
    'allowed_types' => [PhoneNumberType::FIXED_LINE], // Array of allowed types
    'format' => PhoneNumberFormat::INTERNATIONAL, // The value format
  ],
];
```
