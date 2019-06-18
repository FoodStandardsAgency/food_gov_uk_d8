<?php

// Docker local.
$aliases['docker'] = array(
  'env' => 'docker',
  'uri' => 'https://' . getenv('D4D_HOSTNAME'),
  'root' => '/var/www/html/docroot',
  'path-aliases' => array(
    '%drush-script' => '/var/www/html/vendor/bin/drush',
  ),
);

// Application 'foodgovuk', environment 'dev'.
$aliases['dev'] = array (
  'root' => '/var/www/html/foodgovuk.dev/docroot',
  'ac-site' => 'foodgovuk',
  'ac-env' => 'dev',
  'ac-realm' => 'prod',
  'uri' => 'foodgovukdev.prod.acquia-sites.com',
  'path-aliases' => array(
    '%drush-script' => 'drush8',
  ),
);

if (!file_exists('/var/www/html/foodgovuk.dev/docroot')) {
  $aliases['dev']['remote-host'] = 'foodgovukdev.ssh.prod.acquia-sites.com';
  $aliases['dev']['remote-user'] = 'foodgovuk.dev';
}

// Application 'foodgovuk', environment 'test'.
$aliases['test'] = array(
  'root' => '/var/www/html/foodgovuk.test/docroot',
  'ac-site' => 'foodgovuk',
  'ac-env' => 'test',
  'ac-realm' => 'prod',
  'uri' => 'foodgovukstg.prod.acquia-sites.com',
  'path-aliases' => array(
    '%drush-script' => 'drush8',
  ),
);

if (!file_exists('/var/www/html/foodgovuk.test/docroot')) {
  $aliases['test']['remote-host'] = 'foodgovukstg.ssh.prod.acquia-sites.com';
  $aliases['test']['remote-user'] = 'foodgovuk.test';
}

$aliases['ode12'] = array(
  'root' => '/var/www/html/foodgovuk.ode12/docroot',
  'ac-site' => 'foodgovuk',
  'ac-env' => 'ode12',
  'ac-realm' => 'prod',
  'uri' => 'foodgovukode12.prod.acquia-sites.com',
  'path-aliases' => array(
      '%drush-script' => 'drush8',
    ),
);

if (!file_exists('/var/www/html/foodgovuk.ode12/docroot')) {
  $aliases['ode12']['remote-host'] = 'foodgovukode12.ssh.prod.acquia-sites.com';
  $aliases['ode12']['remote-user'] = 'foodgovuk.ode12';
}

$aliases['ode19'] = array(
  'root' => '/var/www/html/foodgovuk.ode19/docroot',
  'ac-site' => 'foodgovuk',
  'ac-env' => 'ode19',
  'ac-realm' => 'prod',
  'uri' => 'foodgovukode19.prod.acquia-sites.com',
  'path-aliases' => array(
      '%drush-script' => 'drush8',
    ),
);

if (!file_exists('/var/www/html/foodgovuk.ode19/docroot')) {
  $aliases['ode19']['remote-host'] = 'foodgovukode19.ssh.prod.acquia-sites.com';
  $aliases['ode19']['remote-user'] = 'foodgovuk.ode19';
}

$aliases['prod'] = array(
  'root' => '/var/www/html/foodgovuk.prod/docroot',
  'ac-site' => 'foodgovuk',
  'ac-env' => 'prod',
  'ac-realm' => 'prod',
  'uri' => 'www.food.gov.uk',
  'path-aliases' => array(
      '%drush-script' => 'drush8',
    ),
);

if (!file_exists('/var/www/html/foodgovuk.prod/docroot')) {
  $aliases['prod']['remote-host'] = 'foodgovuk.ssh.prod.acquia-sites.com';
  $aliases['prod']['remote-user'] = 'foodgovuk.prod';
}
