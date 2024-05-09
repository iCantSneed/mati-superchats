<?php

declare(strict_types=1);

namespace Mati\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mati\Ipc\IpcServer;
use Mati\Repository\StreamRepository;
use Mati\Repository\SuperchatRepository;
use Mati\Rumble\ChatClient;
use Mati\Rumble\ChatUrlFetcher;
use Mati\Rumble\RssLivestreamUrlFetcher;
use Mati\Superchat\SuperchatCache;
use Mati\Superchat\SuperchatConverter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand('mati:stream')]
final class MatiStreamCommand extends Command
{
  use LockableTrait;

  public function __construct(
    private readonly IpcServer $ipcServer,
    private readonly RssLivestreamUrlFetcher $livestreamUrlFetcher,
    private readonly ChatUrlFetcher $chatUrlFetcher,
    private readonly ChatClient $chatClient,
    private readonly SuperchatConverter $superchatConverter,
    private readonly SerializerInterface $serializer,
    private readonly SuperchatCache $superchatCache,
    private readonly StreamRepository $streamRepository,
    private readonly SuperchatRepository $superchatRepository,
    private readonly EntityManagerInterface $entityManager,
    private readonly LoggerInterface $logger,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    if (!$this->lock()) {
      $this->logger->notice('An instance of this command is already running');

      return Command::FAILURE;
    }

    if (!$this->ipcServer->init()) {
      return Command::FAILURE;
    }

    if (($livestreamUrl = $this->livestreamUrlFetcher->fetchLivestreamUrl()) === null) {
      return Command::FAILURE;
    }

    if (($chatUrlAndId = $this->chatUrlFetcher->fetchChatUrl($livestreamUrl)) === null) {
      return Command::FAILURE;
    }
    [$chatUrl, $streamId] = $chatUrlAndId;
    $stream = $this->streamRepository->getOrCreateStream($streamId, new \DateTimeImmutable());

    foreach ($this->chatClient->readData($chatUrl) as $rumbleChatData) {
      foreach ($this->superchatConverter->extractSuperchats($rumbleChatData, $stream) as $superchat) {
        $superchatJson = $this->serializer->serialize($superchat, 'json');
        $this->logger->info('Received superchat', ['superchat' => $superchatJson]);

        if (!$this->superchatRepository->persistIfNew($superchat)) {
          $this->logger->warning('Superchat already exists', ['superchat' => $superchat]);

          continue;
        }

        $this->ipcServer->send($superchatJson);
        $this->superchatCache->storeSuperchat($superchat);
      }

      $this->entityManager->flush();
    }

    return Command::SUCCESS;
  }
}
