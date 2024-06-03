<?php

declare(strict_types=1);

namespace Mati\Controller;

use Mati\Dto\RumbleChat\Message;
use Mati\Dto\RumbleChat\RumbleChatData;
use Mati\Dto\RumbleChat\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/dev', condition: "env('APP_ENV') == 'dev'")]
final class DevController extends AbstractController
{
  #[Route('/rss')]
  public function devRssFeed(
    #[MapQueryParameter]
    ?string $start,
    Request $request,
  ): Response {
    $start = $start ?? (string) random_int(0, 0x7FFF_FFFF);
    $baseUrl = $request->getSchemeAndHttpHost();
    $devRumbleVideoLink = $this->generateUrl('dev_rumble_video', ['start' => $start]);
    $rss = <<<EOF
    <?xml version="1.0" ?>
    <rss version="2.0">
    <channel>
      <title>Dev</title>
      <description>Dev</description>
      <link>{$baseUrl}</link>
      <item>
        <title>Dev</title>
        <description>Dev</description>
        <link>{$devRumbleVideoLink}</link>
      </item>
      </channel>
    </rss>
    EOF;

    return new Response($rss);
  }

  #[Route('/rumble-video', name: 'dev_rumble_video')]
  public function devRumbleVideo(#[MapQueryParameter] string $start): Response
  {
    $str = sprintf(
      'RumbleChat("%s","bogus", %d,',
      $this->generateUrl('dev_base', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
      $start,
    );

    return new Response($str);
  }

  #[Route('/chat/{start}/stream')]
  public function devLivestreamChat(string $start, SerializerInterface $serializer): Response
  {
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->setCallback(static function () use ($serializer, $start): void {
      self::sendSuperchat($serializer, $start);

      while (0 === connection_aborted()) {
        self::sendSuperchat($serializer);
        sleep(10);
      }
    });

    return $response;
  }

  #[Route('', name: 'dev_base')]
  public function devLivestreamChatBase(): Response
  {
    throw new NotFoundHttpException();
  }

  private static function sendSuperchat(SerializerInterface $serializer, ?string $id = null): void
  {
    $now = new \DateTimeImmutable();
    $id = $id ?? (string) $now->getTimestamp();

    $message = new Message();
    $message->id = $id;
    $message->time = $now;
    $message->userId = $id;
    $message->text = "Message {$id}";
    $message->rantPriceCents = 100;

    $user = new User();
    $user->id = $id;
    $user->username = "User {$id}";

    $chatData = new RumbleChatData();
    $chatData->messages = [$message];
    $chatData->users = [$user];

    $json = $serializer->serialize($chatData, 'json');
    echo "data: {$json}\n\n";
    flush();
  }
}
