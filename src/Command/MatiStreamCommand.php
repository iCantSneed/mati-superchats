<?php

declare(strict_types=1);

namespace Mati\Command;

use Doctrine\ORM\EntityManagerInterface;
use Mati\Ipc\IpcMessage;
use Mati\Ipc\IpcServer;
use Mati\Ipc\Terminator;
use Mati\Livestream\ChatClient;
use Mati\Livestream\LivestreamInfoCache;
use Mati\Livestream\LivestreamInfoFetcher;
use Mati\MatiConfiguration;
use Mati\Repository\StreamRepository;
use Mati\Repository\SuperchatRepository;
use Mati\Superchat\SuperchatCache;
use Mati\Superchat\SuperchatConverter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand('mati:stream')]
final class MatiStreamCommand extends Command
{
  use LockableTrait;

  public function __construct(
    private readonly IpcServer $ipcServer,
    private readonly LivestreamInfoFetcher $livestreamInfoFetcher,
    private readonly LivestreamInfoCache $livestreamInfoCache,
    private readonly ChatClient $chatClient,
    private readonly SuperchatConverter $superchatConverter,
    private readonly SuperchatCache $superchatCache,
    private readonly StreamRepository $streamRepository,
    private readonly SuperchatRepository $superchatRepository,
    private readonly EntityManagerInterface $entityManager,
    private readonly Terminator $terminator,
    private readonly LoggerInterface $logger,
    #[Autowire(env: MatiConfiguration::ENV_LIVESTREAM_LANDING_URL)]
    private readonly string $livestreamLandingUrl,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    if (!$this->lock(__FILE__)) {
      $this->logger->notice('An instance of this command is already running');

      return Command::FAILURE;
    }

    if (($livestreamInfo = $this->livestreamInfoFetcher->fetchLivestreamInfo($this->livestreamLandingUrl)) === null) {
      $this->livestreamInfoCache->setLivestreamInfo(null);

      return Command::FAILURE;
    }

    if (!$this->ipcServer->init()) {
      return Command::FAILURE;
    }

    $this->livestreamInfoCache->setLivestreamInfo($livestreamInfo);
    $stream = $this->streamRepository->getOrCreateStream($livestreamInfo->chatId, new \DateTimeImmutable());

    foreach ($this->chatClient->readData($livestreamInfo->chatUrl) as $rumbleChatData) {
      $ipcMessage = new IpcMessage();

      if (null !== $rumbleChatData) {
        foreach ($this->superchatConverter->extractSuperchats($rumbleChatData, $stream) as $superchat) {
          $superchatSerialized = serialize($superchat);
          $this->logger->info('Received superchat', ['superchat' => $superchatSerialized]);

          if (!$this->superchatRepository->persistIfNew($superchat)) {
            $this->logger->warning('Superchat already exists', ['superchat' => $superchatSerialized]);

            continue;
          }

          $ipcMessage->superchats[] = $superchat;
          $this->superchatCache->storeSuperchat($superchat);
        }

        $this->entityManager->flush();
      }

      $this->ipcServer->send(serialize($ipcMessage));

      if ($this->terminator->shouldTerminate()) {
        $this->logger->warning('Terminating as commanded');

        return Command::FAILURE;
      }
    }

    $this->livestreamInfoCache->setLivestreamInfo(null);

    return Command::SUCCESS;
  }
}
