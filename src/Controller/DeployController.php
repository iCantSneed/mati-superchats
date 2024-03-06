<?php

declare(strict_types=1);

namespace Mati\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

class DeployController extends AbstractController
{
  #[Route('/deploy', methods: ['POST'], condition: "request.headers.get('X-Mati-Deploy') === env('APP_SECRET')")]
  public function deploy(Request $request, KernelInterface $kernel): Response
  {
    $archive = $request->files->get('archive');
    if (!$archive instanceof UploadedFile) {
      echo "Archive is not UploadedFile???\n";

      throw new \Exception();
    }

    $zipFile = new \ZipArchive();
    $result = $zipFile->open($archive->getPathname());
    if (true !== $result) {
      echo "Cannot open uploaded ZIP file {$archive->getPathname()}: error {$result}\n";

      throw new \Exception();
    }

    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/plain');
    $response->setCallback(static function () use ($zipFile, $kernel): void {
      $dest = \dirname(__FILE__, 3);
      for ($i = 0; $i < $zipFile->numFiles; ++$i) {
        if (0 === $i % 100) {
          echo "Extracting file {$i}/{$zipFile->numFiles}\n";
          static::flush();
        }
        $filename = $zipFile->getNameIndex($i);
        $result = $zipFile->extractTo($dest, $filename);
        if (!$result) {
          echo "Failed to extract file {$filename}\n";

          throw new \Exception();
        }
      }
      $zipFile->close();
      echo "Extracted all files\n";
      static::flush();

      $commands = [
        'cache:clear' => ['--env' => 'prod'],
        'doctrine:migrations:migrate' => [],
      ];
      $application = new Application($kernel);
      $application->setAutoExit(false);

      $output = new StreamOutput(fopen('php://stdout', 'w'));
      foreach ($commands as $command => $args) {
        $input = new ArrayInput(['command' => $command, ...$args]);
        $result = $application->run($input, $output);
        static::flush();
        if (0 !== $result) {
          throw new \Exception();
        }
      }
    });

    return $response;
  }

  private static function flush(): void
  {
    while (ob_get_level() > 0) {
      ob_end_flush();
    }
    flush();
  }
}
