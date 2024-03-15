<?php

declare(strict_types=1);

namespace Mati\Ipc;

use Mati\MatiConfiguration;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class IpcServer
{
  use SocketTrait;

  public function __construct(
    private readonly LoggerInterface $logger,
    #[Autowire(MatiConfiguration::PARAM_IPC_PORT)]
    private readonly int $ipcPort,
  ) {
    // Do nothing.
  }

  public function init(): bool
  {
    if (!$this->socketCreate()) {
      return false;
    }

    if (!$this->socketSetOption(SO_BROADCAST, 'SO_BROADCAST')) {
      return false;
    }

    return true;
  }

  public function send(string $message): void
  {
    \assert(null !== $this->sock);

    $result = socket_sendto(
      $this->sock,
      $message,
      \strlen($message),
      0,
      MatiConfiguration::IPC_ADDRESS,
      $this->ipcPort
    );
    $this->logger->debug('IpcServer: sent message', [
      'message' => $message,
      'result' => $result,
    ]);
  }
}
