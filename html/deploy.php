<?php

declare(strict_types=1);

use Composer\Console\Application as ComposerApplication;
use Mati\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
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

  chdir('..');
  putenv('COMPOSER_HOME='.dirname(__DIR__).'/var/cache/composer');
  $input = new ArrayInput(['command' => 'install', '--no-dev' => true, '--optimize-autoloader' => true]);
  $application = new ComposerApplication();
  $application->setAutoExit(false);
  $application->setCatchExceptions(false);
  echo "Running composer install\n";
  $application->run($input);
  while (ob_get_level() > 0) {
    ob_end_flush();
  }
  flush();
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
