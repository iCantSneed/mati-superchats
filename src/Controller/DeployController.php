<?php

declare(strict_types=1);

namespace Mati\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

class DeployController extends AbstractController
{
  #[Route('/deploy', methods: ['POST'], condition: "request.headers.get('X-Mati-Deploy') === env('APP_SECRET')")]
  public function deploy(Request $request): Response
  {
    $archive = $request->files->get('archive');
    \assert($archive instanceof UploadedFile);
    $zipFile = new \ZipArchive();
    $result = $zipFile->open($archive->getPathname());
    if (true !== $result) {
      echo "Cannot open uploaded ZIP file {$archive->getPathname()}: error {$result}\n";

      throw new \Exception();
    }

    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/plain');
    $response->setCallback(static function () use ($zipFile): void {
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
      echo "Extracted all files\n";
      static::flush();
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
