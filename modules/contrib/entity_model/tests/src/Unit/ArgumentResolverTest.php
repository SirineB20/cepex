<?php

namespace Drupal\Tests\entity_model\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\entity_model\Controller\ArgumentResolver\ModelValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Symfony\Component\Routing\Route;

/**
 * Tests the argument resolver.
 *
 * @group entity_model
 */
class ArgumentResolverTest extends UnitTestCase {

  /**
   * The argument resolver.
   *
   * @var \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface
   */
  protected $argumentResolver;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $config = $this->createMock(ImmutableConfig::class);
    $config->expects($this->any())
      ->method('get')
      ->with('resolve_form_state_argument_type')
      ->willReturn(TRUE);

    $configFactory = $this->createMock(ConfigFactoryInterface::class);
    $configFactory->expects($this->any())
      ->method('get')
      ->with('entity_model.settings')
      ->willReturn($config);

    $this->argumentResolver = new ArgumentResolver(
          new ArgumentMetadataFactory(),
          [new ModelValueResolver($configFactory)]
      );
  }

  /**
   * Tests whether the arguments are resolved by their name.
   */
  public function testResolveControllerArguments(): void {
    $foo = $this->createMock(MockModel::class);
    $baz = $this->createMock(MockModel::class);
    $mockFormState = $this->createMock(FormStateInterface::class);

    $request = new Request();
    $request->attributes->set('foo_bar', $foo);
    $request->attributes->set('bazQux', $baz);
    $request->attributes->set('form_state', $mockFormState);
    $request->attributes->set('_route', 'entity.mock.canonical');
    $request->attributes->set('_route_object', new Route('some-path'));

    $controllerClass = new class {

      /**
       * An example controller method.
       */
      public function show(MockModel $bazQux, MockModel $fooBar, FormStateInterface $formState): void {
      }

    };

    $controller = [$controllerClass, 'show'];

    $arguments = $this->argumentResolver->getArguments($request, $controller);

    static::assertSame($baz, $arguments[0]);
    static::assertSame($foo, $arguments[1]);
    static::assertSame($mockFormState, $arguments[2]);
  }

  /**
   * Tests whether the argument resolving falls back to matching by type.
   */
  public function testItFallbacksOnType(): void {
    $mockEntity = $this->createMock(MockModel::class);
    $mockFormState = $this->createMock(FormStateInterface::class);

    $request = new Request();
    $request->attributes->set('bazqux', $mockFormState);
    $request->attributes->set('foobar', $mockEntity);
    $request->attributes->set('_route', 'entity.mock.canonical');
    $request->attributes->set('_route_object', new Route('some-path'));

    $controllerClass = new class {

      /**
       * An example controller method.
       */
      public function show(MockModel $totallyDifferentName, FormStateInterface $alsoDifferent): void {
      }

    };

    $controller = [$controllerClass, 'show'];

    $arguments = $this->argumentResolver->getArguments($request, $controller);

    static::assertSame($mockEntity, $arguments[0]);
    static::assertSame($mockFormState, $arguments[1]);
  }

  /**
   * Tests whether the argument resolving does not work if not configured to.
   */
  public function testItDoesNotResolveIfNotConfiguredTo(): void {
    $mockEntity = $this->createMock(MockModel::class);
    $mockFormState = $this->createMock(FormStateInterface::class);

    $request = new Request();
    $request->attributes->set('bazqux', $mockFormState);
    $request->attributes->set('foobar', $mockEntity);
    $request->attributes->set('_route_object', new Route('some-path'));

    $controllerClass = new class {

      /**
       * An example controller method.
       */
      public function show(MockModel $totallyDifferentName, FormStateInterface $alsoDifferent): void {
      }

    };

    $controller = [$controllerClass, 'show'];

    $this->expectExceptionMessageMatches('#^Controller ".+" requires that you provide a value for the "\$totallyDifferentName" argument\.#');
    $this->argumentResolver->getArguments($request, $controller);
  }

  /**
   * Tests whether the argument resolving works if the route option is set.
   */
  public function testItWorksWithCustomRouteOption(): void {
    $mockEntity = $this->createMock(MockModel::class);
    $mockFormState = $this->createMock(FormStateInterface::class);

    $request = new Request();
    $request->attributes->set('bazqux', $mockFormState);
    $request->attributes->set('foobar', $mockEntity);
    $request->attributes->set('_route_object', (new Route('some-path'))->setOption('_enable_fuzzy_argument_resolving', TRUE));

    $controllerClass = new class {

      /**
       * An example controller method.
       */
      public function show(MockModel $totallyDifferentName, FormStateInterface $alsoDifferent): void {
      }

    };

    $controller = [$controllerClass, 'show'];

    $arguments = $this->argumentResolver->getArguments($request, $controller);

    static::assertSame($mockEntity, $arguments[0]);
    static::assertSame($mockFormState, $arguments[1]);
  }

}

/**
 * An example model class.
 */
class MockModel extends ContentEntityBase {
}
