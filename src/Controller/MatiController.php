<?php

declare(strict_types=1);

namespace Mati\Controller;

use Mati\Entity\Stream;
use Mati\MatiConfiguration;
use Mati\Repository\SuperchatRepository;
use Mati\Superchat\SuperchatRenderer;
use Mati\Superchat\SuperchatStreamer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\UX\Turbo\TurboBundle;

final class MatiController extends AbstractController
{
  // TODO cache this route
  #[Route('/')]
  public function index(): Response
  {
    return $this->render('index.html.twig');
  }

  #[Route('/live', name: 'mati_live')]
  public function live(SuperchatStreamer $superchatStreamer): Response
  {
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->setCallback(static fn () => $superchatStreamer->streamEvents());

    return $response;
  }

  #[Route(
    '/archive/{dateString}',
    condition: "request.headers.get('X-Mati-Archive') == env('".MatiConfiguration::ENV_ARCHIVE_SECRET."')"
  )
  ]
  public function archive(
    SuperchatRepository $superchatRepository,
    SerializerInterface $serializer,
    string $dateString,
  ): Response {
    $date = \DateTimeImmutable::createFromFormat('Y-m-d|', $dateString);
    if (false === $date) {
      throw new NotFoundHttpException();
    }

    $superchats = $superchatRepository->findByDate($date);
    if (empty($superchats)) {
      return new Response();
    }

    $csv = $serializer->serialize($superchats, 'csv');

    return new Response($csv, headers: ['Content-Type' => 'text/csv']);
  }

  // TODO cache this route
  #[Route(
    '/prev/{stream}',
    name: 'mati_prev',
    condition: 'request.getPreferredFormat() == "'.TurboBundle::STREAM_FORMAT.'"'
  )]
  public function prev(
    Stream $stream,
    Request $request,
    SuperchatRepository $superchatRepository,
    SuperchatRenderer $renderer,
  ): Response {
    $superchats = $superchatRepository->findBy(['stream' => $stream]);
    \assert(!empty($superchats) && array_is_list($superchats));
    $template = $renderer->prependSuperchats($superchats);
    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

    return new Response($template);
  }
}
