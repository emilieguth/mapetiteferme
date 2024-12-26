<?php
Database::addServer([
  'type' => 'MySQL',
  'host' => 'mysql-ctf',
  'port' => 3306,
  'login' => 'root',
  'password' => '',
  'bases' => ['dev_comptetaferme']
]);

Setting::set('selling\remoteKey', '[KEY]');

RedisCache::addServer('default', 'redis-ctf', 6379, ['timeout' => 2]);

?>