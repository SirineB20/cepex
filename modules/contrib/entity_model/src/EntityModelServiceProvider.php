<?php

namespace Drupal\entity_model;

use Drupal\Core\Config\BootstrapConfigStorageFactory;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\entity_model\Session\AccountProxy;
use Symfony\Component\DependencyInjection\Reference;

/**
 * A service provider for the Entity Model module.
 */
class EntityModelServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $config = BootstrapConfigStorageFactory::get()->read('entity_model.settings');

    if (!empty($config['override_account_proxy'])) {
      $container->getDefinition('current_user')
        ->setClass(AccountProxy::class);
    }

    $argumentResolver = $container->getDefinition('http_kernel.controller.argument_resolver');
    $argumentValueResolvers = $argumentResolver->getArgument(1);
    array_unshift($argumentValueResolvers, new Reference('entity_model.argument_resolver'));
    $argumentResolver->setArgument(1, $argumentValueResolvers);
  }

}
