<?php

declare(strict_types=1);

namespace Mati\Command;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Mati\Ipc\IpcServer;
use Mati\Rumble\ChatClient;
use Mati\Rumble\ChatUrlFetcher;
use Mati\Rumble\RssLivestreamUrlFetcher;
use Mati\Superchat\SuperchatConverter;
use Mati\Superchat\SuperchatRenderer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('mati:stream')]
final class MatiStreamCommand extends Command
{
  use LockableTrait;

  public function __construct(
    private IpcServer $ipcServer,
    private RssLivestreamUrlFetcher $livestreamUrlFetcher,
    private ChatUrlFetcher $chatUrlFetcher,
    private ChatClient $chatClient,
    private SuperchatConverter $superchatConverter,
    private SuperchatRenderer $superchatRenderer,
    private EntityManagerInterface $entityManager,
    private ManagerRegistry $doctrine,
    private LoggerInterface $logger,
  ) {
    parent::__construct();
  }

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

    if (($chatUrl = $this->chatUrlFetcher->fetchChatUrl($livestreamUrl)) === null) {
      return Command::FAILURE;
    }

    $em = $this->entityManager;
    foreach ($this->chatClient->readData($chatUrl) as $rumbleChatData) {
      foreach ($this->superchatConverter->extractSuperchats($rumbleChatData) as $superchat) {
        $superchatJson = $this->superchatRenderer->toJson($superchat);
        $this->logger->info('Received superchat', ['superchat' => $superchatJson]);

        try {
          $em->persist($superchat);
          $em->flush();
          $this->ipcServer->send($superchatJson);
        } catch (UniqueConstraintViolationException $e) {
          $this->logger->warning('Superchat already exists', ['exception' => $e]);
          $em = new EntityManager($em->getConnection(), $em->getConfiguration());
        }
      }
    }

    return Command::SUCCESS;
  }
}
