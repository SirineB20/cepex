<?php
namespace Drupal\rct_globe\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RctGlobeController extends ControllerBase {
  
  protected $entityTypeManager;
  
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }
  
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function globePage() {
   $nodes = $this->entityTypeManager->getStorage('node')
  ->loadByProperties(['type' => 'rct', 'status' => 1]);
    $data = [];
    
    foreach ($nodes as $node) {
      $location = $node->field_location_pays_etranger->getValue();
  if (!empty($location)) {
    $lat = $location[0]['lat'];
    $lon = $location[0]['lon'];
    if (!$node->hasField('field_adress_test')) {
    \Drupal::logger('rct_globe')->error('Field field_adress_test missing for node %nid.', ['%nid' => $node->id()]);
  }
    if (!$node->hasField('field_adress_pays_etranger')) {
    \Drupal::logger('rct_globe')->error('Field field_adress_pays_etranger missing for node %nid.', ['%nid' => $node->id()]); }
    if (!$node->hasField('field_telephone_pays_etranger')) {
    \Drupal::logger('rct_globe')->error('Field field_telephone_pays_etranger missing for node %nid.', ['%nid' => $node->id()]); }
    if (!$node->hasField('field_whatsapp_pays_etranger')) {
    \Drupal::logger('rct_globe')->error('Field field_whatsapp_pays_etranger missing for node %nid.', ['%nid' => $node->id()]); }
    if (!$node->hasField('field_mail_pays_etranger')) {
    \Drupal::logger('rct_globe')->error('Field field_mail_pays_etranger missing for node %nid.', ['%nid' => $node->id()]); }
    $name = $node->hasField('field_adress_test') && !$node->field_adress_test->isEmpty() ? $node->field_adress_test->value : '';
    $address = $node->hasField('field_adress_pays_etranger') && !$node->field_adress_pays_etranger->isEmpty() ? $node->field_adress_pays_etranger->value : '';
    $phone = $node->hasField('field_telephone_pays_etranger') && !$node->field_telephone_pays_etranger->isEmpty() ? $node->field_telephone_pays_etranger->value : '';
    $whatsapp = $node->hasField('field_whatsapp_pays_etranger') && !$node->field_whatsapp_pays_etranger->isEmpty() ? $node->field_whatsapp_pays_etranger->value : '';
    $email = $node->hasField('field_mail_pays_etranger') && !$node->field_mail_pays_etranger->isEmpty() ? $node->field_mail_pays_etranger->value : '';
    $data[] = [
      'latitude' => $lat,
      'longitude' => $lon,
      'name' => $name,
      'address' => $address,
      'phone' => $phone,
      'whatsapp' => $whatsapp,
      'email' => $email, // Adjust if email field is different
    ];
   }
      else{
        $this->messenger()->addWarning('Le nœud ' . $node->id() . ' n\'a pas de localisation.');
      }
    }
    return [
      '#theme' => 'rct_globe',
      '#attached' => [
        'library' => [
          'rct_globe/amcharts5',
        ],
        'drupalSettings' => [
          'rctGlobe' => [
            'data' => $data,
          ],
        ],
      ],
    ];
  }
}
$build['#attached']['drupalSettings']['rctGlobe']['data'] = [
  [
    'name' => 'Bureau 1',
    'latitude' => 48.8566,
    'longitude' => 2.3522,
    'address' => 'Adresse du bureau 1',
    'phone' => '+123456789',
    'email' => 'contact@bureau1.com'
  ],
  // Autres bureaux...
];