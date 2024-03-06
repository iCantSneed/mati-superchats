<?php

declare(strict_types=1);

namespace Mati\Ipc;

use Psr\Log\LoggerInterface;

final class IpcServer
{
  use SocketTrait;

  private ?\SysvSemaphore $sem = null;

  public function __construct(
    private readonly IpcParameters $ipcParameters,
    private readonly LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  public function init(): bool
  {
    if (($this->sem = sem_get($this->ipcParameters->semkey)) === false) {
      $this->logger->error('IpcServer: sem_get: failure');

      return false;
    }

    if (false === sem_acquire($this->sem, true)) {
      $this->logger->warning('IpcServer: sem_acquire: busy');

      return false;
    }

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
      IpcParameters::IPC_ADDRESS,
      $this->ipcParameters->port
    );
    $this->logger->debug('IpcServer: sent message', [
      'message' => $message,
      'result' => $result,
    ]);
  }
}
