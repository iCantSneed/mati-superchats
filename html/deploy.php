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

  $runOrDie = static function (BaseApplication $application, array $parameters, BufferedOutput $output): void {
    $result = $application->run(new ArrayInput($parameters), $output);
    if (0 !== $result) {
      http_response_code(500);
      echo $output->fetch();

      exit;
    }
  };

  $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
  $application = new Application($kernel);
  $application->setAutoExit(false);
  $output = new BufferedOutput();

  switch ($_POST['stage'] ?? '') {
    case '1':
      chdir('..');
      putenv('COMPOSER_HOME='.dirname(__DIR__).'/var/cache/composer');
      $parameters = ['command' => 'install', '--no-dev' => true, '--optimize-autoloader' => true];
      $composerApplication = new ComposerApplication();
      $composerApplication->setAutoExit(false);
      $composerApplication->setCatchExceptions(false);
      $runOrDie($composerApplication, $parameters, $output);
      chdir('html');

      $runOrDie($application, ['command' => 'cache:clear', '-n' => true], $output);

      break;

    case '2':
      $runOrDie($application, ['command' => 'doctrine:migrations:migrate', '-n' => true], $output);

      break;

    default:
      http_response_code(404);

      exit;
  }

  echo $output->fetch();
  echo '(((OK)))';
};
