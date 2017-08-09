<?php
// If the .vagrant folder exists find the ssh key for the virtual machine
if (getenv('WKV_SITE_ENV' == 'local')) {
  $home = drush_server_home();
  // Solve the key file to use
  $path = explode('/', dirname(__FILE__));
  array_pop($path);
  array_pop($path);
  $path[] = '.vagrant';
  $path = implode('/', $path);
  $key = shell_exec('find ' . $path . ' -iname private_key');
  if (!$key) {
    $key = $home . '/.vagrant.d/insecure_private_key';
  }
  $key = rtrim($key);

} else {
  // .vagrant directory doesn't exist, just use empty key
  $key = "";
}

$aliases['local'] = array(
  'parent' => '@parent',
  'site' => 'fsa',
  'env' => 'vagrant',
  'root' => '/vagrant/drupal/web',
  'remote-host' => 'local.food.gov.uk',
  'remote-user' => 'vagrant',
  'ssh-options' => '-i ' . $key,
  'path-aliases' => array(
    '%files' => '/vagrant/drupal/files',
    '%dump-dir' => '/home/vagrant',
  ),
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
  'remote-host' => 'www.food.gov.uk',
  'root' => '/var/www/www.food.gov.uk/web',
  'path-aliases' => array(
    '%dump-dir' => '/home/www-admin',
  ),
  'command-specific' => array(
    'sql-sync' => array(
      'no-cache' => TRUE,
    ),
  ),
);
