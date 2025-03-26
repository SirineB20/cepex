<?php

namespace Drupal\entity_model\Controller\ArgumentResolver;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * An argument resolver for entities and optionally form states.
 */
class ModelValueResolver implements ValueResolverInterface {

  /**
   * Constructs a new ModelValueResolver object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface|null $configFactory
   *   The config factory.
   */
  public function __construct(
    protected ?ConfigFactoryInterface $configFactory = NULL,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(Request $request, ArgumentMetadata $argument): iterable {
    // We want to typehint $formState instead of $form_state.
    // @see https://www.drupal.org/project/drupal/issues/3006502
    if (is_a($argument->getType(), FormStateInterface::class, TRUE)) {
      $config = $this->getConfigFactory()->get('entity_model.settings');

      if (!empty($config->get('resolve_form_state_argument_type'))) {
        yield from $this->doResolve($request, $argument);
      }
    }

    if (is_a($argument->getType(), ContentEntityInterface::class, TRUE)) {
      $isCanonicalRoute = (bool) preg_match(
        '#entity\.(.+)\.(canonical|preview_link|preview|latest_version)#',
        $request->attributes->get('_route')
      );
      $hasRouteOption = (bool) $request->attributes->get('_route_object')->getOption('_enable_fuzzy_argument_resolving');

      if ($isCanonicalRoute || $hasRouteOption) {
        yield from $this->doResolve($request, $argument);
      }
    }
  }

  /**
   * Resolves the argument based on the request.
   */
  protected function doResolve(Request $request, ArgumentMetadata $argument): iterable {
    // Look for an exact match based on type and name.
    if ($attribute = $request->attributes->get($argument->getName())) {
      if ($this->isTypeMatch($attribute, $argument)) {
        yield $attribute;

        return;
      }
    }

    // Look for a match based on type and snake cased name.
    $snakeName = $this->camelCaseToSnakeCase($argument->getName());
    if ($attribute = $request->attributes->get($snakeName)) {
      if ($this->isTypeMatch($attribute, $argument)) {
        yield $attribute;

        return;
      }
    }

    // Look for a match based on type only.
    foreach ($request->attributes->getIterator() as $attribute) {
      if ($this->isTypeMatch($attribute, $argument)) {
        yield $attribute;

        return;
      }
    }

    if ($argument->hasDefaultValue()) {
      yield $argument->getDefaultValue();

      return;
    }

    yield NULL;
  }

  /**
   * Checks whether an attribute matches the type of the argument.
   */
  protected function isTypeMatch($attribute, ArgumentMetadata $argument): bool {
    return is_object($attribute) && is_a($attribute, $argument->getType());
  }

  /**
   * Converts the casing of a string from camelCase to snake_case.
   */
  protected function camelCaseToSnakeCase(string $string): string {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
  }

  /**
   * Gets the config factory.
   *
   * The service is lazy-loaded to avoid a circular dependency.
   */
  protected function getConfigFactory(): ConfigFactoryInterface {
    return $this->configFactory ?? \Drupal::configFactory();
  }

}
