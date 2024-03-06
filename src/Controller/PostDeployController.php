<?php

declare(strict_types=1);

namespace Mati\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

class PostDeployController extends AbstractController
{
  #[Route('/postdeploy', condition: "request.headers.get('X-Mati-Postdeploy') === env('APP_SECRET')")]
  public function postDeploy(): Response
  {
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/plain');
    $response->setCallback(static function (): void {
      echo "Running post-deploy actions\n";
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
