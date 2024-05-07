<?php

declare(strict_types=1);

namespace Mati\Controller;

use Mati\Dto\RumbleChat\Message;
use Mati\Dto\RumbleChat\RumbleChatData;
use Mati\Dto\RumbleChat\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class DevController extends AbstractController
{
  #[Route('/api/dev/rss')]
  public function devRssFeed(): Response
  {
    $prefix = (string) random_int(PHP_INT_MIN, PHP_INT_MAX);
    $devRumbleVideoLink = "http://localhost/api/dev/rumble-video?{$prefix}";
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
  public function devRumbleVideo(): Response
  {
    $chatId = $_ENV['DEV_CHAT_ID'] ?? (string) random_int(0, PHP_INT_MAX);
    $str = "RumbleChat(\"http://localhost/api/dev\", {$chatId},";

    return new Response($str);
  }

  #[Route('/api/dev/chat/{chatId}/stream')]
  public function devLivestreamChat(SerializerInterface $serializer): Response
  {
    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->setCallback(static function () use ($serializer): void {
      while (0 === connection_aborted()) {
        $now = new \DateTimeImmutable();
        $id = (string) $now->getTimestamp();

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

        sleep(10);
      }
    });

    return $response;
  }
}
