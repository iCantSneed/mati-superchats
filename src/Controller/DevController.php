<?php

declare(strict_types=1);

namespace Mati\Controller;

use Mati\Dto\RumbleChat\Message;
use Mati\Dto\RumbleChat\RumbleChatData;
use Mati\Dto\RumbleChat\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class DevController extends AbstractController
{
  #[Route('/api/dev/rss')]
  public function devRssFeed(#[MapQueryParameter] ?string $start = null): Response
  {
    $start = $start ?? (string) random_int(0, (int) 0xffff_ffff_ffff_ffff_ffff_ff);
    $devRumbleVideoLink = "http://localhost/api/dev/rumble-video?start={$start}";
    $rss = <<<EOF
    <?xml version="1.0" ?>
    <rss version="2.0">
    <channel>
      <title>Dev</title>
      <description>Dev</description>
      <link>http://localhost</link>
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

  #[Route('/api/dev/rumble-video')]
  public function devRumbleVideo(#[MapQueryParameter] string $start): Response
  {
    $str = "RumbleChat(\"http://localhost/api/dev\", {$start},";

    return new Response($str);
  }

  #[Route('/api/dev/chat/{start}/stream')]
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
