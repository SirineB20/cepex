<?php
namespace Drupal\exporter_sync;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\user\Entity\User;

/**
 * Service pour synchroniser les exportateurs.
 */
class ExporterSyncService {

  protected ClientInterface $httpClient;
  protected ConfigFactoryInterface $configFactory;
  protected $logger;
  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(
    ClientInterface $http_client,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('exporter_sync');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Synchronise un exportateur vers le site cible.
   */
  public function syncExporter(array $exporter_data): bool {
    $config = $this->configFactory->get('exporter_sync.settings');
    
    if (!$config->get('auto_sync_enabled')) {
      return FALSE;
    }

    $target_url = $config->get('target_site_url');
    $api_key = $config->get('api_key');

    if (!$target_url || !$api_key) {
      $this->logger->error('Configuration manquante: URL cible ou clé API');
      return FALSE;
    }

    try {
      $response = $this->httpClient->post($target_url . '/api/exporter/create', [
        'headers' => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $api_key,
          'Accept' => 'application/json',
        ],
        'json' => $exporter_data,
        'timeout' => 30,
      ]);

      $success = in_array($response->getStatusCode(), [200, 201]);
      
      if ($success && $config->get('log_sync_events')) {
        $this->logger->info('Exportateur synchronisé: @email', [
          '@email' => $exporter_data['email'] ?? 'Unknown',
        ]);
      }

      return $success;

    } catch (RequestException $e) {
      $this->logger->error('Erreur synchronisation: @message', [
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Prépare les données d'un exportateur pour la synchronisation.
   */
  public function prepareExporterData(User $user): array {
    return [
      'email' => $user->getEmail(),
      'username' => $user->getAccountName(),
      'status' => $user->isActive() ? 1 : 0,
      'matricul_fiscale' => $user->hasField('field_matricul_fiscale') 
        ? ($user->get('field_matricul_fiscale')->value ?? '') 
        : '',
      'nom_d_entreprise' => $user->hasField('field_nom_d_entreprise') 
        ? ($user->get('field_nom_d_entreprise')->value ?? '') 
        : '',
      'created' => $user->getCreatedTime(),
      'roles' => $user->getRoles(),
    ];
  }

  /**
   * Synchronise automatiquement lors de la création/mise à jour d'un utilisateur.
   */
  public function autoSyncUser(User $user): bool {
    if (!$user->hasRole('exportateur')) {
      return FALSE;
    }

    $data = $this->prepareExporterData($user);
    return $this->syncExporter($data);
  }
}

// 7. Formulaire de configuration (src/Form/ExporterSyncConfigForm.php)
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
