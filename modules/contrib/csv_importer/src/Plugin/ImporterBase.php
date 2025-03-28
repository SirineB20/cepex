<?php

namespace Drupal\csv_importer\Plugin;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileExists;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\file\FileRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\file\FileUsage\FileUsageInterface;

/**
 * Provides a base class for ImporterBase plugins.
 *
 * @see \Drupal\csv_importer\Annotation\Importer
 * @see \Drupal\csv_importer\Plugin\ImporterManager
 * @see \Drupal\csv_importer\Plugin\ImporterInterface
 * @see plugin_api
 */
abstract class ImporterBase extends PluginBase implements ImporterInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The file repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The logger factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs ImporterBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config service.
   * @param \Drupal\file\FileRepositoryInterface $file_repository
   *   The file repository service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  final public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config, FileRepositoryInterface $file_repository, ModuleHandlerInterface $module_handler, LoggerChannelFactoryInterface $logger_factory, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->config = $config;
    $this->fileRepository = $file_repository;
    $this->moduleHandler = $module_handler;
    $this->loggerFactory = $logger_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('file.repository'),
      $container->get('module_handler'),
      $container->get('logger.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function data() {
    $csv = $this->configuration['csv'];
    $return = [];

    if ($csv && is_array($csv)) {
      $csv_fields = $csv[0];
      unset($csv[0]);
      foreach ($csv as $index => $data) {
        foreach ($data as $key => $content) {
          if (empty($content)) {
            continue;
          }

          if (isset($csv_fields[$key])) {
            $content = Unicode::convertToUtf8($content, mb_detect_encoding($content));
            $fields = explode('|', $csv_fields[$key]);

            if (preg_match(static::REGEX_MULTIPLE, $content, $matches)) {
              if (isset($matches[2])) {
                $content = explode('+', $matches[2]);
              }
            }

            $field = $fields[0];
            if (count($fields) > 1) {
              foreach ($fields as $key => $in) {
                $return['content'][$index][$field][$in] = $content;
              }
            }
            elseif (isset($return['content'][$index][$field])) {
              $prev = $return['content'][$index][$field];
              $return['content'][$index][$field] = [];

              if (is_array($prev)) {
                $prev[] = $content;
                $return['content'][$index][$field] = $prev;
              }
              else {
                $return['content'][$index][$field][] = $prev;
                $return['content'][$index][$field][] = $content;
              }
            }
            else {
              $return['content'][$index][current($fields)] = $content;
            }
          }
        }

        if (isset($return[$index])) {
          $return['content'][$index] = array_intersect_key($return[$index], array_flip($this->configuration['fields']));
        }
      }
    }

    $this->moduleHandler->invokeAll('csv_importer_pre_import', [&$return]);

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  // Method to create or get existing taxonomy term
  protected function createOrGetTaxonomyTerm($term_name, $vocabulary, $parent_tid = NULL) {
    if (empty($term_name)) {
      return NULL;
    }

    // Try to find existing term first
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
      'name' => $term_name,
      'vid' => $vocabulary,
    ]);

    // If term exists, return its ID
    if (!empty($terms)) {
      $term = reset($terms);
      
      // Check if parent matches if parent is specified
      if ($parent_tid && $term->parent->target_id != $parent_tid) {
        // Create a new term with the correct parent
        return $this->createTaxonomyTerm($term_name, $vocabulary, $parent_tid);
      }
      
      return $term->id();
    }

    // Create new term
    return $this->createTaxonomyTerm($term_name, $vocabulary, $parent_tid);
  }

  protected function createTaxonomyTerm($term_name, $vocabulary, $parent_tid = NULL) {
    $term = \Drupal\taxonomy\Entity\Term::create([
      'name' => $term_name,
      'vid' => $vocabulary,
      'parent' => $parent_tid ? [$parent_tid] : [],
    ]);

    try {
      $term->save();
      \Drupal::logger('importer_csv')->notice('Terme de taxonomie créé : @name dans le vocabulaire @vocab', [
        '@name' => $term_name,
        '@vocab' => $vocabulary
      ]);
      return $term->id();
    } catch (\Exception $e) {
      \Drupal::logger('importer_csv')->error('Erreur lors de la création du terme : @error', [
        '@error' => $e->getMessage()
      ]);
      return NULL;
    }
  }
  public function add($contents, array &$context) {
    \Drupal::logger('importer_csv')->notice('Début du processus d\'importation');
    if (!$contents) {
      return NULL;
    }
  
    // Vérifier si nous devons initialiser le processus
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($contents);
      $context['sandbox']['contents'] = array_values($contents); // Réindexer le tableau pour s'assurer que les indices commencent à 0
    }
  
    // Vérifier s'il reste des éléments à traiter
    if ($context['sandbox']['progress'] >= $context['sandbox']['max']) {
      $context['finished'] = 1;
      return;
    }
  
    // Récupérer l'élément actuel
    $current_index = $context['sandbox']['progress'];
    $content = $context['sandbox']['contents'][$current_index];
    
    // Mettre à jour le message de progression
    $context['message'] = $this->t('Import entity %index out of %max', [
      '%index' => $current_index + 1,
      '%max' => $context['sandbox']['max'],
    ]);
    
  
    $entity_type = $this->configuration['entity_type'];
    $entity_type_bundle = $this->configuration['entity_type_bundle'];
    $entity_definition = $this->entityTypeManager->getDefinition($entity_type);
  
    if ($entity_definition->hasKey('bundle') && $entity_type_bundle) {
      $content[$entity_definition->getKey('bundle')] = $this->configuration['entity_type_bundle'];
    }
  
    foreach ($content as $key => $item) {
      if (is_string($item) && file_exists($item)) {
        $created = $this->fileRepository->writeData(file_get_contents($item), $this->config->get('system.file')->get('default_scheme') . '://' . basename($item), FileExists::Replace);
        $content[$key] = $created->id();
      }
    }
  
    /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $entity_storage */
    $entity_storage = $this->entityTypeManager->getStorage($this->configuration['entity_type']);
    
    try {
      $entity = NULL;
      if (!empty($content[$entity_definition->getKey('id')])) {
        $entity = $entity_storage->load($content[$entity_definition->getKey('id')]);
      }
      
      $languages = $this->languageManager->getLanguages();
      $langcode_default = $this->languageManager->getDefaultLanguage()->getId();
      $langcode = $this->languageManager->isMultilingual() && isset($content['langcode']) && isset($languages[$content['langcode']]) ? $content['langcode'] : $langcode_default;
      
      if ($entity) {
        // Check if entity supports translations
        if ($entity instanceof \Drupal\Core\Entity\TranslatableInterface) {
          // Handle existing entity with translation
          if ($entity->hasTranslation($langcode)) {
            $translation = $entity->getTranslation($langcode);
          }
          else {
            $translation = $entity->addTranslation($langcode);
          }
          
          // Set field values on the translation
          foreach ($content as $field => $value) {
            if ($field !== 'langcode') {
              $translation->set($field, $value);
            }
          }
          
          if ($translation->save()) {
            $id = $entity->id();
            $context['results']['updated'][] = $id;
            
            if ($langcode_default !== $langcode) {
              $context['results']['translations'][] = $id;
            }
          }
        }
        else {
          // Handle non-translatable entities
          foreach ($content as $field => $value) {
            if ($field !== 'langcode') {
              $entity->set($field, value: $value);
            }
          }
          
          if ($entity->save()) {
            $id = $entity->id();
            $context['results']['updated'][] = $id;
          }
        }
      }
      else {
        // Initialize variables
        $email = '';
        $matricule_fiscal = '';
        $Nom_entreprise = '';
        $Directeur_general= '';
        $Telephone = '';
        $Fax = '';
        $Site_web = '';
        $found_email = false;
        $found_matricule = false;
        $secteur_name = '';
        $filiere_name = '';
        $produit_name = '';
        
        // First pass: Scan for email and matricule fiscal
        foreach ($content as $field => $value) {
          // Check for email field
          if (str_contains($field, 'E-MAIL')) {
            preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/', $value, $matches);
            if (!empty($matches)) {
              $email = $matches[0];
              $found_email = true;
              \Drupal::logger('importer_csv')->notice('Email trouvé : @value', ['@value' => $email]);
            }
          }
          
          // Check for matricule fiscal field
          if (str_contains($field, 'Matricule fiscal')) {
            $matricule_fiscal = $value;
            $found_matricule = true;
            \Drupal::logger('importer_csv')->notice('Champ : @field, Valeur : @value', ['@field' => $field, '@value' => $value]);
          }

        if (str_contains($field, 'Nom entreprise')) {
          $Nom_entreprise = $value;
          \Drupal::logger('importer_csv')->notice('Champ : @field, Valeur : @value', ['@field' => $field, '@value' => $value]);
        }
        if (str_contains($field, 'Directeur général')) {
          $Directeur_general = $value;
          \Drupal::logger('importer_csv')->notice('Champ : @field, Valeur : @value', ['@field' => $field, '@value' => $value]);
        }
        if (str_contains($field, 'Téléphone')) {
          $Telephone = $value;
          \Drupal::logger('importer_csv')->notice('Champ : @field, Valeur : @value', ['@field' => $field, '@value' => $value]);
        }
        if (str_contains($field, 'FAX')) {
          $Fax = $value;
          \Drupal::logger('importer_csv')->notice('Champ : @field, Valeur : @value', ['@field' => $field, '@value' => $value]);
        }
        if (str_contains($field, 'Site web')) {
          $Site_web = $value;
          \Drupal::logger('importer_csv')->notice('Champ : @field, Valeur : @value', ['@field' => $field, '@value' => $value]);
        }
        if (str_contains($field, 'SECTEUR')) {
          $secteur_name = trim($value);
        }
        if (str_contains($field, 'Filières')) {
          $filiere_name = trim($value);
        }
        if (str_contains($field, 'Produit D.A')) {
          $produit_name = trim($value);
        }
      }
        // Create taxonomy terms hierarchy
      $secteur_tid = NULL;
      $filiere_tid = NULL;
      $produit_tid = NULL;

      // Create or get Secteur term
      if (!empty($secteur_name)) {
        $secteur_tid = $this->createOrGetTaxonomyTerm($secteur_name, 'secteurs2');
      }

      // Create or get Filière term under Secteur
      if (!empty($filiere_name) && $secteur_tid) {
        $filiere_tid = $this->createOrGetTaxonomyTerm($filiere_name, 'secteurs2', $secteur_tid);
      }

      // Create or get Produit term under Filière
      if (!empty($produit_name) && $filiere_tid) {
        $produit_tid = $this->createOrGetTaxonomyTerm($produit_name, 'secteurs2', $filiere_tid);
      }

      // Prepare term IDs for field_secteurs2
      $term_ids = array_filter([$secteur_tid, $filiere_tid, $produit_tid]);

        // After checking all fields, see if we have both required values
        if ($found_email && $found_matricule) {
          $existing_users = \Drupal::entityTypeManager()
          ->getStorage('user')
          ->loadByProperties([
            'name' => $matricule_fiscal
          ]);

        // Si un utilisateur avec ce matricule fiscal existe déjà, utilisez son ID
        if (!empty($existing_users)) {
          \Drupal::logger('entity_importer')->notice('Utilisateur avec matricule fiscal @matricule existe déjà', [
            '@matricule' => $matricule_fiscal
          ]);
          
          $existing_user = reset($existing_users);
          $user_id = $existing_user->id();
        } else {
          // Generate a random password
          $password = bin2hex(random_bytes(8));  // 16 characters length password
          
          // Create user
          $user = User::create([
            'name' => $matricule_fiscal,  // Username
            'mail' => $email,     // Email
            'pass' => $password,  // Random password
            'status' => 0,        // 1 means active
            'roles' => ['exportateur'],  // Assign role 'exportateur'
          ]);
          $user->set('field_password', $password); // Set the password field
          // Save the user
          if ($user->save()) {
            $user_id = $user->id();
            // Create entity with the user already assigned as owner
            $content['uid'] = $user_id; // Set the user as owner before creating the entity
            $content['status'] = 0; // Unpublish the entity
            //maping the fields
            $content['field_e_mail'] = $email; // Set the email mapping to the email field
            $content['field_matricule_fiscale'] = $matricule_fiscal; // Set the matricule fiscal mapping to the matricule field
            $content['field_nom_de_l_entreprise'] = $Nom_entreprise; // Set the name of entreprise maping to the title field
            $content['field_directeur_general'] = $Directeur_general; // Set the name of the general manager mapping to the title field
            $content['field_tel'] = $Telephone; // Set the telephone mapping to the title field
            $content['field_fax'] = $Fax; // Set the fax mapping to the title field
            $content['field_site_web'] = $Site_web; // Set the site web mapping to the title field
            $content['field_secteurs2'] = $term_ids; // Set the term IDs mapping to the title field
            $entity = $entity_storage->create($content);
            if ($entity->save()) {
              $id = $entity->id();
              if (!isset($context['results']['created'])) {
                $context['results']['created'] = [];
              }
              $context['results']['created'][] = $id;
              \Drupal::logger('importer_csv')->notice('Entité créée avec ID: @id', ['@id' => $id]);
            } else {
              \Drupal::logger('entity_importer')->error('Échec de création de l\'entité après création de l\'utilisateur');
            }
          } else {
            \Drupal::logger('entity_importer')->error('Échec de création de l\'utilisateur bien que email et matricule fiscal soient présents');
          }
        }} else {
          // Log what's missing
          if (!$found_email && !$found_matricule) {
            \Drupal::logger('entity_importer')->warning('Failed to create user: Missing email and matricule fiscal');
          } else if (!$found_email) {
            \Drupal::logger('entity_importer')->warning('Failed to create user: Missing email');
          } else {
            \Drupal::logger('entity_importer')->warning('Failed to create user: Missing matricule fiscal');
          }
        }
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('entity_importer')->error('Error importing entity: @message', ['@message' => $e->getMessage()]);
    }
    
    // Incrémenter le compteur pour le prochain élément
    $context['sandbox']['progress']++;
    
    // Indiquer si nous avons terminé
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }

  /**
   * {@inheritdoc}
   */
  public function finished($success, array $results, array $operations) {
    if ($success) {
      $added_count = isset($results['added']) ? count($results['added']) : 0;
      $updated_count = isset($results['updated']) ? count($results['updated']) : 0;
      $translation_count = isset($results['translations']) ? count($results['translations']) : 0;

      $this->messenger()->addMessage(
        $this->t('@added_count new content added, @updated_count updated and translations created for @translations_count content.', [
          '@added_count' => $added_count,
          '@updated_count' => $updated_count,
          '@translations_count' => $translation_count,
        ]),
      );
    }
    else {
      $this->messenger()->addError($this->t('The import process encountered errors.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    if ($data = $this->data()) {
      $process['operations'][] = [
        [$this, 'add'],
        [$data['content']],
      ];

      $process['finished'] = [$this, 'finished'];
      batch_set($process);
    }
    else {
      $this->messenger()->addError($this->t('The import process encountered errors. No data is available for processing. Please check the CSV file and ensure it is saved in UTF-8 format.'));
    }
  }

}
