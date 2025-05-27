<?php
namespace Drupal\exporter_sync\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Formulaire de configuration pour Exporter Sync.
 */
class ExporterSyncConfigForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['exporter_sync.settings'];
  }

  public function getFormId() {
    return 'exporter_sync_config_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('exporter_sync.settings');

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Clé API'),
      '#default_value' => $config->get('api_key'),
      '#description' => $this->t('Clé secrète pour authentifier les requêtes API.'),
      '#required' => TRUE,
    ];

    $form['target_site_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL du site cible'),
      '#default_value' => $config->get('target_site_url'),
      '#description' => $this->t('URL complète du site cepex (ex: https://cepex.example.com)'),
      '#required' => TRUE,
    ];

    $form['auto_sync_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Synchronisation automatique'),
      '#default_value' => $config->get('auto_sync_enabled'),
      '#description' => $this->t('Synchroniser automatiquement lors de la création/modification d\'exportateurs.'),
    ];

    $form['log_sync_events'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Journaliser les événements'),
      '#default_value' => $config->get('log_sync_events'),
      '#description' => $this->t('Enregistrer les événements de synchronisation dans les logs.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('exporter_sync.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('target_site_url', rtrim($form_state->getValue('target_site_url'), '/'))
      ->set('auto_sync_enabled', $form_state->getValue('auto_sync_enabled'))
      ->set('log_sync_events', $form_state->getValue('log_sync_events'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}