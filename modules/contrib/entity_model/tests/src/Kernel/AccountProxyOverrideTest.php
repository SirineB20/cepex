<?php

namespace Drupal\Tests\entity_model\Kernel;

use Drupal\Core\Config\MemoryStorage;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Site\Settings;
use Drupal\entity_model\Session\AccountProxy;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\UserInterface;

/**
 * Tests the account proxy overrides.
 *
 * @group entity_model
 */
class AccountProxyOverrideTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_model',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->setUpCurrentUser();
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    new Settings([
      'bootstrap_config_storage' => function () {
        $storage = new MemoryStorage();
        $storage->write('entity_model.settings', [
          'override_account_proxy' => TRUE,
        ]);
        return $storage;
      },
      'hash_salt' => $this->randomMachineName(),
    ]);

    parent::register($container);
  }

  /**
   * Tests whether the account proxy class is overridden.
   */
  public function testIsAccountProxyOverridden(): void {
    $this->container
      ->get('config.factory')
      ->getEditable('entity_model.settings')
      ->set('override_account_proxy', TRUE)
      ->save();

    $this->container->get('module_handler')->reload();

    self::assertInstanceOf(
      AccountProxy::class,
      $this->container->get('current_user')
    );

    self::assertInstanceOf(
      UserInterface::class,
      $this->container->get('current_user')->getAccount()
    );
  }

}
