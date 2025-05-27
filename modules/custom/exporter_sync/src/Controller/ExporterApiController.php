<?php
namespace Drupal\exporter_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller pour l'API d'exportateurs.
 */
class ExporterApiController extends ControllerBase {

  /**
   * Crée ou met à jour un exportateur.
   */
  public function createExporter(Request $request): JsonResponse {
    // Vérification de l'authentification
    $auth_header = $request->headers->get('Authorization');
    if (!$this->validateApiKey($auth_header)) {
      return new JsonResponse(['error' => 'Unauthorized'], 401);
    }

    $data = json_decode($request->getContent(), TRUE);
    
    if (!$data || !isset($data['email'])) {
      return new JsonResponse(['error' => 'Invalid data - email required'], 400);
    }

    try {
      // Vérifier si l'utilisateur existe
      $existing_users = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->loadByProperties(['mail' => $data['email']]);

      if (!empty($existing_users)) {
        $user = reset($existing_users);
        $this->updateExporterUser($user, $data);
        $message = 'Exporter updated successfully';
      } else {
        $user = $this->createExporterUser($data);
        $message = 'Exporter created successfully';
      }

      // Créer ou mettre à jour le profil exportateur
      $profile_node = $this->createOrUpdateExporterProfile($user, $data);

      return new JsonResponse([
        'message' => $message,
        'user_id' => $user->id(),
        'profile_id' => $profile_node->id(),
        'status' => 'success'
      ], 201);

    } catch (\Exception $e) {
      \Drupal::logger('exporter_sync')->error('Error creating exporter: @message', [
        '@message' => $e->getMessage(),
      ]);
      
      return new JsonResponse(['error' => 'Internal server error'], 500);
    }
  }

  /**
   * Valide la clé API.
   */
  protected function validateApiKey(?string $auth_header): bool {
    if (!$auth_header || !str_starts_with($auth_header, 'Bearer ')) {
      return FALSE;
    }

    $token = substr($auth_header, 7);
    $config = $this->config('exporter_sync.settings');
    $valid_key = $config->get('api_key');

    return !empty($valid_key) && hash_equals($valid_key, $token);
  }

  /**
   * Crée un nouvel utilisateur exportateur.
   */
 protected function createExporterUser(array $data): User {
  // Utiliser le matricule fiscal pour générer le username si pas fourni
  $username = $data['username'] ?? $this->generateUsername($data['matricule_fiscale']);
  
  $user = User::create([
    'name' => $username,
    'mail' => $data['email'],
    'status' => $data['status'] ?? 1,
    'roles' => ['exportateur'],
  ]);

  $user->save();
  return $user;
}


  /**
   * Met à jour un utilisateur exportateur existant.
   */
  protected function updateExporterUser(User $user, array $data): void {
    // Ajouter le rôle exportateur s'il ne l'a pas
    if (!$user->hasRole('exportateur')) {
      $user->addRole('exportateur');
    }

    // Mettre à jour le nom d'utilisateur si fourni
    if (isset($data['username'])) {
      $user->set('name', $data['username']);
    }
    
    if (isset($data['status'])) {
      $user->set('status', $data['status']);
    }

    $user->save();
  }

  /**
   * Crée ou met à jour le profil exportateur (nœud profile_exportateur).
   */
  protected function createOrUpdateExporterProfile(User $user, array $data): Node {
    // Chercher un nœud profile_exportateur existant pour cet utilisateur
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'profile_exportateur')
      ->condition('uid', $user->id())
      ->accessCheck(FALSE);
    
    $nids = $query->execute();
    
    if (!empty($nids)) {
      // Mettre à jour le profil existant
      $profile_node = Node::load(reset($nids));
    } else {
      // Créer un nouveau profil
      $profile_node = Node::create([
        'type' => 'profile_exportateur',
        'title' => $data['nom_d_entreprise'] ?: $user->getDisplayName(),
        'uid' => $user->id(),
        'status' => 1,
      ]);
    }

    // Mettre à jour les champs du profil
    if (isset($data['matricul_fiscale'])) {
      $profile_node->set('field_matricul_fiscale', $data['matricul_fiscale']);
    }
        if (isset($data['gouvernorat'])) {
      $profile_node->set('field_gouvernorat', $data['gouvernorat']);
    }
    
    if (isset($data['directeur_general'])) {
      $profile_node->set('field_directeur_general', $data['directeur_general']);
    }
    if (isset($data['adresse'])) {
      $profile_node->set('field_adresse', $data['adresse']);
    }
    if (isset($data['telephone'])) {
      $profile_node->set('field_telephone', $data['telephone']);
    }
   
    if (isset($data['nom_d_entreprise'])) {
      $profile_node->set('field_nom_d_entreprise', $data['nom_d_entreprise']);
      // Mettre à jour aussi le titre
      $profile_node->set('title', $data['nom_d_entreprise']);
    }
    $profile_node->save();
    return $profile_node;
  }

  /**
   * Génère un nom d'utilisateur unique basé sur l'email.
   */
 protected function generateUsername(string $matricule_fiscal): string {
  // Utiliser le matricule fiscal comme base pour le nom d'utilisateur
  $base_username = $matricule_fiscal;
  $username = $base_username;
  $counter = 1;

  while ($this->usernameExists($username)) {
    $username = $base_username . '_' . $counter;
    $counter++;
  }

  return $username;
}

  /**
   * Vérifie si un nom d'utilisateur existe.
   */
  protected function usernameExists(string $username): bool {
    $users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(['name' => $username]);
    
    return !empty($users);
  }

  /**
   * Récupère un exportateur par ID utilisateur.
   */
  public function getExporter(Request $request, $user_id = null): JsonResponse {
    // Vérification de l'authentification
    $auth_header = $request->headers->get('Authorization');
    if (!$this->validateApiKey($auth_header)) {
      return new JsonResponse(['error' => 'Unauthorized'], 401);
    }

    try {
      if (!$user_id) {
        return new JsonResponse(['error' => 'User ID required'], 400);
      }

      $user = User::load($user_id);
      if (!$user || !$user->hasRole('exportateur')) {
        return new JsonResponse(['error' => 'Exporter not found'], 404);
      }

      // Récupérer le profil exportateur associé
      $query = \Drupal::entityQuery('node')
        ->condition('type', 'profile_exportateur')
        ->condition('uid', $user->id())
        ->accessCheck(FALSE);
      
      $nids = $query->execute();
      $profile_data = [];
      
      if (!empty($nids)) {
        $profile_node = Node::load(reset($nids));
        $profile_data = [
          'profile_id' => $profile_node->id(),
          'matricul_fiscale' => $profile_node->get('field_matricul_fiscale')->value ?? '',
          'nom_d_entreprise' => $profile_node->get('field_nom_d_entreprise')->value ?? '',
          'directeur_general' => $profile_node->get('field_directeur_general')->value ?? '',
          'adresse' => $profile_node->get('field_adresse')->value ?? '',
          'telephone' => $profile_node->get('field_telephone')->value ?? '',
          'gouvernorat' => $profile_node->get('field_gouvernorat')->value ?? '',
        ];
      }

      return new JsonResponse([
        'user_id' => $user->id(),
        'email' => $user->getEmail(),
        'username' => $user->getAccountName(),
        'status' => $user->isActive() ? 1 : 0,
        'created' => $user->getCreatedTime(),
        'profile' => $profile_data,
      ], 200);

    } catch (\Exception $e) {
      \Drupal::logger('exporter_sync')->error('Error getting exporter: @message', [
        '@message' => $e->getMessage(),
      ]);
      
      return new JsonResponse(['error' => 'Internal server error'], 500);
    }
  }
}