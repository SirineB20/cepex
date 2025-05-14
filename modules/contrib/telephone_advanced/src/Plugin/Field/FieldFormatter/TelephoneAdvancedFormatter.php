<?php

namespace Drupal\telephone_advanced\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\telephone_advanced\FieldSettings;
use Drupal\telephone_advanced\TelephoneFormatterInterface;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a formatter for the telephone field.
 *
 * @FieldFormatter(
 *   id = "telephone_advanced",
 *   label = @Translation("Formatted"),
 *   field_types = {
 *     "telephone",
 *   },
 * )
 */
class TelephoneAdvancedFormatter extends FormatterBase {

  /**
   * The telephone formatter.
   *
   * @var \Drupal\telephone_advanced\TelephoneFormatterInterface
   */
  protected $telephoneFormatter;

  /**
   * Class constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   The third party settings.
   * @param \Drupal\telephone_advanced\TelephoneFormatterInterface $telephone_formatter
   *   The telephone formatter.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, TelephoneFormatterInterface $telephone_formatter) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->telephoneFormatter = $telephone_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('telephone_advanced.telephone_formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return parent::isApplicable($field_definition) && FieldSettings::isEnabled($field_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'format' => PhoneNumberFormat::NATIONAL,
      'link' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['format'] = [
      '#type' => 'select',
      '#title' => $this->t('Format'),
      '#options' => $this->getFormatOptions(),
      '#default_value' => $this->getSetting('format'),
      '#required' => TRUE,
    ];

    $form['link'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link to the provided telephone number'),
      '#default_value' => (int) $this->getSetting('link'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $format = $this->getSetting('format');

    $summary[] = $this->t('Use the @format format.', [
      '@format' => mb_strtolower($this->getFormatOptions()[$format]),
    ]);

    if ($this->getSetting('link')) {
      $summary[] = $this->t('Link to the provided telephone number.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    $default_country = FieldSettings::getDefaultCountry($items->getFieldDefinition());
    $format = $this->getSetting('format');
    $link = $this->getSetting('link');

    foreach ($items as $delta => $item) {
      try {
        $value_text = $this->telephoneFormatter->format($item->value, $format, $default_country);
        $value_link = $value_text;

        if ($link && $format != PhoneNumberFormat::E164) {
          $value_link = $this->telephoneFormatter->format($item->value, PhoneNumberFormat::E164, $default_country);
        }
      }
      catch (NumberParseException $ex) {
        $value_text = $item->value;
        $value_link = NULL;
      }

      if ($link && $value_link) {
        $element[$delta] = [
          '#type' => 'link',
          '#title' => $value_text,
          '#url' => Url::fromUri('tel:' . $value_link),
        ];
      }
      else {
        $element[$delta] = [
          '#markup' => $value_text,
        ];
      }
    }

    return $element;
  }

  /**
   * Get the format options.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   The format options.
   */
  protected function getFormatOptions() {
    return [
      PhoneNumberFormat::NATIONAL => $this->t('National'),
      PhoneNumberFormat::INTERNATIONAL => $this->t('International'),
      PhoneNumberFormat::E164 => $this->t('E164'),
      PhoneNumberFormat::RFC3966 => $this->t('RFC 3966'),
    ];
  }

}
