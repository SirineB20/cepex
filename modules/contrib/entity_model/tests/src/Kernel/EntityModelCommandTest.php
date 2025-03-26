<?php

namespace Drupal\Tests\entity_model\Kernel;

use Drupal\entity_model\Commands\EntityModelCommands;
use Drush\Style\DrushStyle;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Tests the Drush commands.
 *
 * @group entity_model
 */
class EntityModelCommandTest extends ModelPluginTestBase {

  /**
   * The entity model commands.
   *
   * @var \Drupal\entity_model\Commands\EntityModelCommands
   */
  private $command;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();
    if (!class_exists(DrushStyle::class)) {
      // Drush appears to dynamically load the DrushStyle class if Symfony 6 is
      // installed, so we need to do the same here or we get a fatal error.
      // @see https://github.com/drush-ops/drush/pull/5108/files
      $drushStyleClassFilePath = DRUPAL_ROOT . '/../vendor/drush/drush/src-symfony-compatibility/v6/Style/DrushStyle.php';
      if (file_exists($drushStyleClassFilePath)) {
        require_once $drushStyleClassFilePath;
      }
    }
  }

    /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createContentType(['type' => 'unmapped_page']);

    $command = new EntityModelCommands(
      $this->container->get('entity_type.manager'),
      $this->container->get('plugin.manager.entity_model.model')
    );
    $this->command = $command;
  }

  /**
   * Tests 'entity_model:list' command output.
   */
  public function testListModels(): void {
    // Make sure we can access the command output.
    $this->command->setOutput($output = new BufferedOutput());

    // Execute the command.
    $this->command->listModels();

    // Assert the output.
    $output = $output->fetch();
    static::assertStringContainsString('Model "node.page" is mapped against "Drupal\entity_model_test\Entity\Node\Page"', $output);
    static::assertStringContainsString('Model "node.unmapped_page" is not mapped', $output);
  }

  /**
   * Tests 'entity_model:list --filter-mapped-status=mapped' command output.
   */
  public function testListModelsOnlyMapped(): void {
    // Make sure we can access the command output.
    $this->command->setOutput($output = new BufferedOutput());

    // Execute the command.
    $this->command->listModels(['filter-mapped-status' => 'mapped']);

    // Assert the output.
    $output = $output->fetch();
    static::assertStringContainsString('Model "node.page" is mapped against "Drupal\entity_model_test\Entity\Node\Page"', $output);
    static::assertStringNotContainsString('Model "node.unmapped_page" is not mapped', $output);
  }

  /**
   * Tests 'entity_model:list --filter-mapped-status=unmapped' command output.
   */
  public function testListModelsOnlyUnMapped(): void {
    // Make sure we can access the command output.
    $this->command->setOutput($output = new BufferedOutput());

    // Execute the command.
    $this->command->listModels(['filter-mapped-status' => 'unmapped']);

    // Assert the output.
    $output = $output->fetch();
    static::assertStringNotContainsString('Model "node.page" is mapped against "Drupal\entity_model_test\Entity\Node\Page"', $output);
    static::assertStringContainsString('Model "node.unmapped_page" is not mapped', $output);
  }

}
