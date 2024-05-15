<?php

declare(strict_types=1);

namespace Mati\Controller;

use Mati\Repository\SuperchatRepository;
use Mati\Superchat\SuperchatStreamer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

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

  #[Route('/api/archive')]
  public function archive(
    SuperchatRepository $superchatRepository,
    SerializerInterface $serializer,
    #[MapQueryParameter('date')]
    ?string $dateString = null,
  ): Response {
    if (null === $dateString) {
      $date = new \DateTimeImmutable('yesterday');
    } else {
      $date = \DateTimeImmutable::createFromFormat('Y-m-d|', $dateString);
      if (false === $date) {
        throw new NotFoundHttpException();
      }
    }

    $superchats = $superchatRepository->findByDate($date);
    if (empty($superchats)) {
      return new Response('', Response::HTTP_NO_CONTENT);
    }

    $csv = $serializer->serialize($superchats, 'csv');

    return new Response($csv, headers: ['Content-Type' => 'text/csv']);
  }
}
