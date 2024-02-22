<?php

declare(strict_types=1);

namespace Mati\Ipc;

use Psr\Log\LoggerInterface;

final class IpcClient
{
  private ?\Socket $sock = null;

  public function __construct(
    private readonly IpcParameters $ipcParameters,
    private readonly LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  public function init(): bool
  {
    if (($this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) {
      $err = socket_last_error();
      $this->logger->error('IpcClient: socket_create: failure', [
        'code' => $err,
        'reason' => socket_strerror($err),
      ]);

      return false;
    }

    if (false === socket_set_option($this->sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
      $err = socket_last_error();
      $this->logger->error('IpcClient: socket_set_option: failed to set SO_REUSEADDR', [
        'code' => $err,
        'reason' => socket_strerror($err),
      ]);

      return false;
    }

    if (false === socket_bind($this->sock, IpcParameters::IPC_ADDRESS, $this->ipcParameters->port)) {
      $err = socket_last_error();
      $this->logger->error('IpcClient: socket_bind: failure', [
        'code' => $err,
        'reason' => socket_strerror($err),
      ]);

      return false;
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
