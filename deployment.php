<?php

declare(strict_types=1);
use Deployment\Deployer;
use Deployment\Helpers;
use Deployment\Logger;
use Deployment\Server;

return [
  'log' => './var/log/deployment.log',
  'mati' => [
    'remote' => $_ENV['FTP_REMOTE'],
    'include' => '
      /bin
      /config
      /html
      /migrations
      /src
      .env
      composer.json
    ',
    'allowDelete' => true,
    'before' => [
      static function (Server $server, Logger $logger, Deployer $deployer): bool {
        cleanComposerJsonForProd();

        return true;
      },
    ],
    'after' => [
      static function (Server $server, Logger $logger, Deployer $deployer): bool {
        $out = Helpers::fetchUrl('https://mati.x10.mx/deploy.php', $err, ['secret' => $_ENV['DEPLOYKEY']]);
        if (null !== $out) { // intentionally ==
          $logger->log($out, 'gray', 0);
        }
        if ($err) {
          $logger->log($err, 'red', 0);

          return false;
        }

        return true;
      },
    ],
  ],
];

function cleanComposerJsonForProd(): void
{
  $composerJsonFile = new SplFileObject('composer.json', 'r+');
  $composerJsonRaw = $composerJsonFile->fread($composerJsonFile->getSize());
  $composerJson = json_decode($composerJsonRaw, true);
  assert(is_array($composerJson));
  assert(isset($composerJson['type'], $composerJson['require'], $composerJson['autoload']));
  assert(isset($composerJson['config'], $composerJson['config']['allow-plugins']));
  $newComposerJson = [
    'type' => $composerJson['type'],
    'require' => $composerJson['require'],
    'autoload' => $composerJson['autoload'],
    'config' => $composerJson['config'],
  ];
  foreach ($newComposerJson['config']['allow-plugins'] as &$value) {
    $value = false;
  }
  $newComposerJsonRaw = json_encode($newComposerJson, JSON_UNESCAPED_SLASHES);
  $composerJsonFile->fseek(0);
  $composerJsonFile->fwrite($newComposerJsonRaw);
  $composerJsonFile->ftruncate(strlen($newComposerJsonRaw));
}