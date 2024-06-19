<?php

declare(strict_types=1);

namespace Mati\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
  #[Route('/')]
  public function index(#[Autowire(param: 'kernel.logs_dir')] string $logsDir): Response
  {
    $finder = new Finder();
    $finder->in($logsDir);

    return $this->render('admin.html.twig', [
      'files' => $finder,
    ]);
  }

  #[Route('/log/{logFile}', name: 'admin_read_log')]
  public function readLog(string $logFile, #[Autowire(param: 'kernel.logs_dir')] string $logsDir): Response
  {
    $logFilename = Path::makeAbsolute($logFile, $logsDir);
    if (!file_exists($logFilename)) {
      throw new NotFoundHttpException();
    }

    return new StreamedResponse(static fn () => readfile($logFilename), headers: ['Content-Type' => 'text/plain']);
  }
}
