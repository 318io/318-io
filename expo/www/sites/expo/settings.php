<?php
$db_uname = '[db username]';
$db_passw = '[db password]';

$databases = array();
$config_directories = array();
$settings['hash_salt'] = 'TvAO_ty49W12q7r3hlrH1ZZm1MyvvgW4djQsP59Ac2ak-vgorNQgQmwg7Hm4gb4nfKiXPws2Sw';
$settings['update_free_access'] = FALSE;

$settings['container_yamls'][] = __DIR__ . '/services.yml';

 if (file_exists(__DIR__ . '/settings.local.php')) {
   include __DIR__ . '/settings.local.php';
 }
$databases['default']['default'] = array (
  'database' => 'db_expo',
  'username' => $db_uname,
  'password' => $db_passw,
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
$settings['install_profile'] = 'minimal';
$config_directories['sync'] = 'sites/expo/files/config_mC5gA2M46sI6goatDljvIQV8mzZH9nTY2P9Ww-gZDW-VUbgYpIhESkLgJOqM0PUVeHDYvjF3Dx/sync';
$settings['trusted_host_patterns'] = array(
   '^.*\.318\.test$',
);
