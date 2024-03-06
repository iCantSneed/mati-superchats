<?php

declare(strict_types=1);

namespace Mati\Ipc;

use Psr\Log\LoggerInterface;

final class IpcClient
{
  use SocketTrait;

  public function __construct(
    private readonly IpcParameters $ipcParameters,
    private readonly LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  public function init(): bool
  {
    if (!$this->socketCreate()) {
      return false;
    }

    if (!$this->socketSetOption(SO_REUSEADDR, 'SO_REUSEADDR')) {
      return false;
    }

    if (null === $this->sock || false === socket_bind($this->sock, IpcParameters::IPC_ADDRESS, $this->ipcParameters->port)) {
      return $this->logError('socket_bind: failure');
    }

    return true;
  }

  /**
   * @return iterable<string>
   */
  public function receive(): iterable
  {
    \assert(null !== $this->sock);

    $this->logger->debug('IpcClient: listening for messages');

    while (true) {
      if (false === socket_recvfrom($this->sock, $message, 50000, 0, $host, $port)) {
        $err = socket_last_error();
        $this->logger->error('IpcClient: socket_recvfrom: failure', [
          'code' => $err,
          'reason' => socket_strerror($err),
        ]);

        continue;
      }

      $this->logger->debug('IpcClient: received message', ['message' => $message]);

      yield $message;
    }
  }
}
