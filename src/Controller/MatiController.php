<?php

declare(strict_types=1);

namespace Mati\Controller;

use Mati\Superchat\SuperchatStreamer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

final class MatiController extends AbstractController
{
  #[Route('/api/live')]
  public function live(SuperchatStreamer $superchatStreamer): Response
  {
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->setCallback(static fn () => $superchatStreamer->streamEvents());

    return $response;
  }
}
