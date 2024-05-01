<?php

declare(strict_types=1);

use Composer\Console\Application as ComposerApplication;
use Mati\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context): void {
  $deploykey = require dirname(__FILE__, 2).'/.deploykey';
  $matiDeployParam = $_POST['secret'] ?? '';
  if ($matiDeployParam !== $deploykey) {
    http_response_code(404);

    exit;
  }

  $runOrDie = static function (BaseApplication $application, ArrayInput $input, BufferedOutput $output): void {
    $result = $application->run($input, $output);
    if (0 !== $result) {
      http_response_code(500);
      echo $output->fetch();

      exit;
    }
  };

  $output = new BufferedOutput();

  chdir('..');
  putenv('COMPOSER_HOME='.dirname(__DIR__).'/var/cache/composer');
  $input = new ArrayInput(['command' => 'install', '--no-dev' => true, '--optimize-autoloader' => true]);
  $application = new ComposerApplication();
  $application->setAutoExit(false);
  $application->setCatchExceptions(false);
  $runOrDie($application, $input, $output);
  chdir('html');

  $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
  $application = new Application($kernel);
  $application->setAutoExit(false);

  $commands = [
    ['command' => 'doctrine:migrations:migrate', '-n' => true],
    ['command' => 'cache:warmup', '-n' => true],
  ];
  foreach ($commands as $params) {
    $input = new ArrayInput($params);
    $runOrDie($application, $input, $output);
  }

  echo $output->fetch();
  echo '(((OK)))';
};
