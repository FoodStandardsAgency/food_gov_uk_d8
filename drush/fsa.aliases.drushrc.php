<?php

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
