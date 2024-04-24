<?php

declare(strict_types=1);

use Mati\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

if (!isset($_GLOBALS['mati_deployed'])) {
  $envs = require dirname(__FILE__, 2).'/.env.local.php';
  $appSecret = $envs['APP_SECRET'];
  $matiDeployHeader = $_SERVER['HTTP_X_MATI_DEPLOY'] ?? '';
  if ($matiDeployHeader !== $appSecret) {
    header('404 Not Found');

    return;
  }

  $archiveFilename = $_FILES['archive']['tmp_name'];
  $zipFile = new ZipArchive();
  $result = $zipFile->open($archiveFilename);
  if (true !== $result) {
    throw new Exception("Cannot open uploaded ZIP file {$archiveFilename}: error {$result}");
  }

  header('Content-Type: text/plain');
  $dest = dirname(__FILE__, 2);
  for ($i = 0; $i < $zipFile->numFiles; ++$i) {
    if (0 === $i % 100) {
      echo "Extracting file {$i}/{$zipFile->numFiles}\n";
      while (ob_get_level() > 0) {
        ob_end_flush();
      }
      flush();
    }
    $filename = $zipFile->getNameIndex($i);
    $result = $zipFile->extractTo($dest, $filename);
    if (!$result) {
      echo "Failed to extract file {$filename}\n";

      throw new Exception();
    }
  }
  $zipFile->close();
  echo "Extracted all files\n";
  while (ob_get_level() > 0) {
    ob_end_flush();
  }
  flush();

  $_GLOBALS['mati_deployed'] = true;

  require_once dirname(__DIR__).'/vendor/autoload_runtime.php';
}

return static function (array $context): void {
  $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
  $application = new Application($kernel);
  $application->setAutoExit(false);

  $commands = [
    ['command' => 'doctrine:migrations:migrate', '-n' => true],
    ['command' => 'cache:warmup', '-n' => true],
  ];
  foreach ($commands as $params) {
    $input = new ArrayInput($params);
    $output = new BufferedOutput();
    echo "Running {$params['command']}\n";
    $result = $application->run($input, $output);
    echo $output->fetch();
    echo "Process finished with code {$result}\n";
    while (ob_get_level() > 0) {
      ob_end_flush();
    }
    flush();
    if (0 !== $result) {
      throw new Exception();
    }
  }

  echo "Deployment completed\n";
};
