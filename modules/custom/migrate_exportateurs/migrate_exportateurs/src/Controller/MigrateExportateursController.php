<?php

namespace Drupal\migrate_exportateurs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\EntityInterface;

/**
 * Controller for migrating exportateur user profiles to nodes.
 */
class MigrateExportateursController extends ControllerBase {

  /**
   * Migrates exportateur user profiles to exportateur nodes.
   */
  public function migrate() {
    $uids = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('roles', 'exportateur')
      ->accessCheck(FALSE)
      ->execute();
  
    $count = 0;
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
    $profileStorage = \Drupal::entityTypeManager()->getStorage('profile');
  
    foreach ($uids as $uid) {
      $user = User::load($uid);
      if (!$user) continue;
  
      // Vérifie si un node existe déjà pour ce user
      $existing = \Drupal::entityQuery('node')
        ->condition('type', 'profile_exportateur')
        ->condition('uid', $uid)
        ->range(0, 1)
        ->accessCheck(FALSE)
        ->execute();
  
      if (!empty($existing)) {
        continue;
      }
  
      // Charger le profil associé
      $profiles = $profileStorage->loadByProperties([
        'uid' => $uid,
        'type' => 'profile_exportateur',
      ]);
  
      $profile = reset($profiles);
      if (!$profile) {
        continue;
      }
  
      // Préparer les valeurs du node avec vérification des champs
      $nodeValues = [
        'type' => 'profile_exportateur',
        'title' => $user->getDisplayName(),
        'uid' => $uid,
        'status' => 1, // Publié par défaut
      ];
  
      // Mappage des champs entre profile et node
      $fieldMappings = [
        'field_e_mail' => 'field_e_mail',
        'field_nom_de_l_entreprise' => 'field_nom_d_entreprise',
        'field_adresse' => 'field_adresse',
        'field_directeur_general' => 'field_directeur_general',
        'field_matricule_fiscale' => 'field_matricul_fiscale',
        'field_fax' => 'field_fax',
        'field_tel' => 'field_telephone',
        'field_site_web' => 'field_site_web',
        'field_presentation' => 'field_presentation',
        'field_certifications' => 'field_certifications',
        'field_imageprofile' => 'field_imageprofile',
        'field_secteur1' => 'field_secteur', // Autre nom possible pour secteur
      ];
  
      // Copier tous les champs en préservant leur structure complète
      foreach ($fieldMappings as $profileField => $nodeField) {
        if ($profile->hasField($profileField) && !$profile->get($profileField)->isEmpty()) {
          // Utiliser getValue() pour préserver la structure complète du champ
          // (références d'entité, valeurs multiples, etc.)
          $nodeValues[$nodeField] = $profile->get($profileField)->getValue();
        }
      }
      
      // Ces lignes sont en dehors de la boucle foreach pour éviter de traiter les mêmes champs plusieurs fois
      // Gestion spécifique du champ image
      if ($profile->hasField('field_imageprofile') && !$profile->get('field_imageprofile')->isEmpty()) {
        $nodeValues['field_imageprofile'] = $profile->get('field_imageprofile')->getValue();
      }
      
      // Gérer le champ taxonomie certifications
      if ($profile->hasField('field_certifications') && !$profile->get('field_certifications')->isEmpty()) {
        $nodeValues['field_certifications'] = $profile->get('field_certifications')->getValue();
      }
      
      // Gestion spécifique des champs qui nécessitent un traitement différent
      if ($profile->hasField('field_e_mail') && !$profile->get('field_e_mail')->isEmpty()) {
        $nodeValues['field_e_mail'] = $profile->get('field_e_mail')->value ?? $user->getEmail();
      } else {
        $nodeValues['field_e_mail'] = $user->getEmail();
      }
      
      // Gestion spécifique pour secteur (taxonomie)
      if ($profile->hasField('field_secteur1') && !$profile->get('field_secteur1')->isEmpty()) {
        // Copier directement la structure existante
        $nodeValues['field_secteur'] = $profile->get('field_secteur1')->getValue();
        
        // Si vous avez besoin de vous assurer que tous les niveaux sont inclus:
        $termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
        $termIds = array_column($nodeValues['field_secteur'], 'target_id');
        
        // Pour chaque terme, assurez-vous que ses parents sont également ajoutés
        foreach ($termIds as $termId) {
          $parents = $termStorage->loadAllParents($termId);
          foreach ($parents as $parent) {
            if ($parent->id() != $termId) {
              $nodeValues['field_secteur'][] = ['target_id' => $parent->id()];
            }
          }
        }
        
        // Éliminer les doublons
        $uniqueTerms = [];
        foreach ($nodeValues['field_secteur'] as $termValue) {
          $uniqueTerms[$termValue['target_id']] = $termValue;
        }
        
        $nodeValues['field_secteur'] = array_values($uniqueTerms);
      }
  
      try {
        $node = Node::create($nodeValues);
        $node->save();
        $count++;
        
        // Journaliser la réussite pour faciliter le débogage
        \Drupal::logger('migrate_exportateurs')->notice('Profil exportateur migré pour l\'utilisateur @uid (@name)', [
          '@uid' => $uid,
          '@name' => $user->getDisplayName(),
        ]);
      }
      catch (\Exception $e) {
        \Drupal::logger('migrate_exportateurs')->error('Erreur lors de la migration pour l\'utilisateur @uid: @message', [
          '@uid' => $uid,
          '@message' => $e->getMessage(),
        ]);
        
        // Ajouter des détails supplémentaires pour le débogage
        \Drupal::logger('migrate_exportateurs')->debug('Détails de l\'erreur pour @uid: @trace', [
          '@uid' => $uid,
          '@trace' => $e->getTraceAsString(),
        ]);
      }
    }
  
    $this->messenger()->addMessage($this->t('@count profils exportateur migrés vers les contenus.', ['@count' => $count]));
  
    return [
      '#type' => 'markup',
      '#markup' => $this->t('<p>Migration terminée.</p><p>Consultez les journaux pour plus de détails.</p>'),
    ];
  }
}