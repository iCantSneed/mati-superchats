<?php

declare(strict_types=1);
use Deployment\Deployer;
use Deployment\Helpers;
use Deployment\Logger;
use Deployment\Server;

return [
  'mati' => [
    'remote' => $_ENV['FTP_REMOTE'],
    'include' => '
      /assets
      /bin
      /config
      /html
      /migrations
      /src
      /templates
      .env
      .revision
      composer.json
      composer.lock
      importmap.php
    ',
    'allowDelete' => true,
    'before' => [
      static function (Server $server, Logger $logger, Deployer $deployer): bool {
        cleanComposerJsonForProd();

        return true;
      },
    ],
    'after' => [
      deployStage('1'),
      deployStage('2'),
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
    'replace' => $composerJson['replace'],
    'extra' => $composerJson['extra'],
  ];
  foreach ($newComposerJson['config']['allow-plugins'] as &$value) {
    $value = false;
  }
  $newComposerJsonRaw = json_encode($newComposerJson, JSON_UNESCAPED_SLASHES);
  $composerJsonFile->fseek(0);
  $composerJsonFile->fwrite($newComposerJsonRaw);
  $composerJsonFile->ftruncate(strlen($newComposerJsonRaw));
}

function deployStage(string $stage): callable
{
  return static function (Server $server, Logger $logger, Deployer $deployer) use ($stage): bool {
    $out = Helpers::fetchUrl($_ENV['ROOT_URL'].'/deploy.php', $err, ['secret' => $_ENV['DEPLOYKEY'], 'stage' => $stage]);
    if (null !== $out) { // intentionally ==
      $logger->log($out, 'gray', 0);
    }
    if ($err) {
      $logger->log($err, 'red', 0);

      return false;
    }
    if (!str_ends_with(haystack: $out, needle: '(((OK)))')) {
      $logger->log('Deployment unsuccessful', 'red', 0);

      return false;
    }

    return true;
  };
}
