<?php

declare(strict_types=1);

namespace Mati\Ipc;

use Mati\MatiConfiguration;
use Psr\Log\LoggerInterface;

final class IpcClient
{
  use SocketTrait;

  public function __construct(
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

    if (!$this->socketSetOption(SO_RCVTIMEO, 'SO_RCVTIMEO', ['sec' => 30, 'usec' => 0])) {
      return false;
    }

    if (null === $this->sock || false === socket_bind($this->sock, MatiConfiguration::IPC_ADDRESS, MatiConfiguration::IPC_PORT)) {
      return $this->logError('socket_bind: failure');
    }

    return true;
  }

  /**
   * @return iterable<?string>
   */
  public function receive(): iterable
  {
    \assert(null !== $this->sock);

    $this->logger->debug('IpcClient: listening for messages');

    while (true) {
      if (false === socket_recvfrom($this->sock, $message, 50000, 0, $host, $port)) {
        $err = socket_last_error();
        if (SOCKET_EAGAIN === $err) {
          $this->logger->debug('IpcClient: no data received');

          yield null;
        } else {
          $this->logError('socket_recvfrom: failure');
        }

        continue;
      }

      $this->logger->debug('IpcClient: received message', ['message' => $message]);

      yield $message;
    }
  }
}
