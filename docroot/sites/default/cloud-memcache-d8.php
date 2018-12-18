<?php
/**
 * @file
 * Contains memcache configuration.
 */
use Composer\Autoload\ClassLoader;

/**
 * Use memcache as cache backend.
 *
 * Autoload memcache classes and service container in case module is not
 * installed. Avoids the need to patch core and allows for overriding the
 * default backend when installing Drupal.
 *
 * @see https://www.drupal.org/node/2766509
 */

if (getenv('AH_SITE_ENVIRONMENT') &&
  isset($settings['memcache']['servers'])
) {

// Check for PHP Memcached libraries.
$memcache_exists = class_exists('Memcache', FALSE);
$memcached_exists = class_exists('Memcached', FALSE);
// MODIFY THIS NEXT LINE depending on the location of the memcache Drupal
//   module in your codebase.
$memcache_services_yml = DRUPAL_ROOT . '/modules/contrib/memcache/memcache.services.yml';
$memcache_module_is_present = file_exists($memcache_services_yml);
if ($memcache_module_is_present && ($memcache_exists || $memcached_exists)) {
  // Use Memcached extension if available.
  if ($memcached_exists) {
    $settings['memcache']['extension'] = 'Memcached';
  }
  if (class_exists(ClassLoader::class)) {
    $class_loader = new ClassLoader();
    // MODIFY THIS NEXT LINE depending on the location of the memcache
    // Drupal module in your codebase.
    $class_loader->addPsr4('Drupal\\memcache\\', 'modules/contrib/memcache/src');
    $class_loader->register();
    $settings['container_yamls'][] = $memcache_services_yml;
    // Bootstrap cache.container with memcache rather than database.
    $settings['bootstrap_container_definition'] = [
      'parameters' => [],
      'services' => [
        'database' => [
          'class' => 'Drupal\Core\Database\Connection',
          'factory' => 'Drupal\Core\Database\Database::getConnection',
          'arguments' => ['default'],
        ],
        'settings' => [
          'class' => 'Drupal\Core\Site\Settings',
          'factory' => 'Drupal\Core\Site\Settings::getInstance',
        ],
        'memcache.settings' => [
          'class' => 'Drupal\memcache\MemcacheSettings',
          'arguments' => ['@settings'],
        ],
        'memcache.backend.cache.factory' => [
          'class' => 'Drupal\memcache\Driver\MemcacheDriverFactory',
          'arguments' => ['@memcache.settings'],
        ],
        'memcache.backend.cache.container' => [
          'class' => 'Drupal\memcache\DrupalMemcacheFactory',
          'factory' => ['@memcache.backend.cache.factory', 'get'],
          'arguments' => ['container'],
        ],
        'lock.container' => [
          'class' => 'Drupal\memcache\Lock\MemcacheLockBackend',
          'arguments' => ['container', '@memcache.backend.cache.container'],
        ],
        'cache_tags_provider.container' => [
          'class' => 'Drupal\Core\Cache\DatabaseCacheTagsChecksum',
          'arguments' => ['@database'],
        ],
        'cache.container' => [
          'class' => 'Drupal\memcache\MemcacheBackend',
          'arguments' => [
            'container',
            '@memcache.backend.cache.container',
            '@cache_tags_provider.container',
            '@lock.container',
          ],
        ],
      ],
    ];
    // Override default fastchained backend for static bins.
    // @see https://www.drupal.org/node/2754947
    $settings['cache']['bins']['bootstrap'] = 'cache.backend.memcache';
    $settings['cache']['bins']['discovery'] = 'cache.backend.memcache';
    $settings['cache']['bins']['config'] = 'cache.backend.memcache';
    // Use memcache as the default bin.
    $settings['cache']['default'] = 'cache.backend.memcache';
    // Enable stampede protection.
    $settings['memcache']['stampede_protection'] = TRUE;
    // Move locks to memcache.
    // The memcache.yml file needs to be copied from this documentation:
    //    https://docs.acquia.com/acquia-cloud/performance/memcached/enable/#configuration-for-drupal-8
    // DO MODIFY the following path (relative to DRUPAL_ROOT) depending on where
    //    you placed this file in your codebase.
    $settings['container_yamls'][] = 'sites/default/memcache.yml';
    // Enable compression for PHP 7.
    $settings['memcache']['options'][Memcached::OPT_COMPRESSION] = TRUE;
  }
 }
}
