<?php

namespace Drupal\fsa_webform_error;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Overrides the form_error_handler service to enable fsa form error.
 *
 */
class FsaWebformErrorServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $container->getDefinition('form_error_handler')
      ->setClass(FSAFormErrorHandler::class)
      ->setArguments([
        new Reference('string_translation'),
        new Reference('renderer'),
        new Reference('messenger')
      ]);
  }

}
