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
  'path-aliases' =>
  array (
    '%drush-script' => 'drush8',
  ),
  'dev.livedev' =>
  array (
    'parent' => '@foodgovuk.dev',
    'root' => '/mnt/gfs/foodgovuk.dev/livedev/docroot',
  ),
  'remote-host' => 'foodgovukdev.ssh.prod.acquia-sites.com',
  'remote-user' => 'foodgovuk.dev',
);

// Application 'foodgovuk', environment 'prod'.
$aliases['prod'] = array (
  'root' => '/var/www/html/foodgovuk.prod/docroot',
  'ac-site' => 'foodgovuk',
  'ac-env' => 'prod',
  'ac-realm' => 'prod',
  'uri' => 'https://www.food.gov.uk',
  'path-aliases' =>
  array (
    '%drush-script' => 'drush8',
  ),
  'prod.livedev' =>
  array (
    'parent' => '@foodgovuk.prod',
    'root' => '/mnt/gfs/foodgovuk.prod/livedev/docroot',
  ),
  'remote-host' => 'foodgovuk.ssh.prod.acquia-sites.com',
  'remote-user' => 'foodgovuk.prod',
);

// Application 'foodgovuk', environment 'ra'.
$aliases['ra'] = array (
  'root' => '/var/www/html/foodgovuk.ra/docroot',
  'ac-site' => 'foodgovuk',
  'ac-env' => 'ra',
  'ac-realm' => 'prod',
  'uri' => 'foodgovukra.prod.acquia-sites.com',
  'path-aliases' =>
  array (
    '%drush-script' => 'drush8',
  ),
  'ra.livedev' =>
  array (
    'parent' => '@foodgovuk.ra',
    'root' => '/mnt/gfs/foodgovuk.ra/livedev/docroot',
  ),
  'remote-host' => 'foodgovukra.ssh.prod.acquia-sites.com',
  'remote-user' => 'foodgovuk.ra',
);

// Application 'foodgovuk', environment 'test'.
$aliases['test'] = array (
  'root' => '/var/www/html/foodgovuk.test/docroot',
  'ac-site' => 'foodgovuk',
  'ac-env' => 'test',
  'ac-realm' => 'prod',
  'uri' => 'foodgovukstg.prod.acquia-sites.com',
  'path-aliases' =>
  array (
    '%drush-script' => 'drush8',
  ),
  'test.livedev' =>
  array (
    'parent' => '@foodgovuk.test',
    'root' => '/mnt/gfs/foodgovuk.test/livedev/docroot',
  ),
  'remote-host' => 'foodgovukstg.ssh.prod.acquia-sites.com',
  'remote-user' => 'foodgovuk.test',
);
