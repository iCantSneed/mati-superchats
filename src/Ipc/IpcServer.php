<?php

declare(strict_types=1);

namespace Mati\Ipc;

use Psr\Log\LoggerInterface;

final class IpcServer
{
  private ?\SysvSemaphore $sem = null;
  private ?\Socket $sock = null;

  public function __construct(
    private IpcParameters $ipcParameters,
    private LoggerInterface $logger,
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

    if (($this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) {
      $err = socket_last_error();
      $this->logger->error('IpcServer: socket_create: failure', [
        'code' => $err,
        'reason' => socket_strerror($err),
      ]);

      return false;
    }

    if (false === socket_set_option($this->sock, SOL_SOCKET, SO_BROADCAST, 1)) {
      $err = socket_last_error();
      $this->logger->error('IpcServer: socket_set_option: failed to set SO_BROADCAST', [
        'code' => $err,
        'reason' => socket_strerror($err),
      ]);

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
