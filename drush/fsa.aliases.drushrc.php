<?php

$environment = getenv("WKV_SITE_ENV");
if ($environment == "local") {
  $home = drush_server_home();
  // Solve the key file to use
  $path = explode('/', dirname(__FILE__));
  array_pop($path);
  $path[] = '/../.vagrant';
  $path = implode('/', $path);
  $key = shell_exec('find ' . $path . ' -iname private_key');
  if (!$key) {
    $key = $home . '/.vagrant.d/insecure_private_key';
  }
  $key = rtrim($key);
}

$aliases['local'] = array(
  'parent' => '@parent',
  'site' => 'fsa',
  'env' => 'vagrant',
  'root' => '/vagrant/drupal/web',
  'uri' => 'https://local.food.gov.uk',
  'remote-host' => 'local.food.gov.uk',
  'remote-user' => 'vagrant',
  'ssh-options' => '-i ' . $key,
);

$aliases['dev'] = array(
  'uri' => 'https://fsa.dev.wunder.io',
  'remote-user' => 'www-admin',
  'remote-host' => 'fsa.dev.wunder.io',
  'root' => '/var/www/fsa/current/web',
  'path-aliases' => array(
    '%dump-dir' => '/home/www-admin',
  ),
  'command-specific' => array(
    'sql-sync' => array(
      'no-cache' => TRUE,
    ),
  ),
);

$aliases['stage'] = array(
  'uri' => 'https://fsa.stage.wunder.io',
  'remote-user' => 'www-admin',
  'remote-host' => 'fsa.stage.wunder.io',
  'root' => '/var/www/fsa/current/web',
  'path-aliases' => array(
    '%dump-dir' => '/home/www-admin',
  ),
  'command-specific' => array(
    'sql-sync' => array(
      'no-cache' => TRUE,
    ),
  ),
);

$aliases['prod'] = array(
  'uri' => 'https://www.food.gov.uk',
  'remote-user' => 'www-admin',
  'remote-host' => '83.136.255.30',
  'root' => '/var/www/fsa.prod.wunder.io/current/web',
  'path-aliases' => array(
    '%dump-dir' => '/home/www-admin',
  ),
  'command-specific' => array(
    'sql-sync' => array(
      'no-cache' => TRUE,
    ),
  ),
);
