<?php

return [
  'log' => './var/log/deployment.log',
  'mati' => [
    'remote' => $_ENV['FTP_HOST'],
    'user' => $_ENV['FTP_USER'],
    'password' => $_ENV['FTP_PASS'],
    'include' => '
      /bin
      /config
      /html
      /migrations
      /src
      /vendor
      .env.local.php
      composer.json
    ',
    'before' => [
      'local: composer install --no-dev --optimize-autoloader',
      'local: composer dump-env prod'
    ],
    'afterUpload' => [
      "https://mati.x10.mx/api/postdeploy?secret={$_ENV['FTP_SECRET']}"
    ],
  ]
];
