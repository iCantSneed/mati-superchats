<?php

declare(strict_types=1);

namespace Mati\Ipc;

use Psr\Log\LoggerInterface;

trait SocketTrait
{
  private ?\Socket $sock = null;
  private readonly LoggerInterface $logger;

  private function logError(string $message): bool
  {
    $err = socket_last_error($this->sock);
    $this->logger->error(static::class.': '.$message, [
      'code' => $err,
      'reason' => socket_strerror($err),
    ]);

    return false;
  }

  private function socketCreate(): bool
  {
    if (($this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) {
      $this->logError('socket_create: failure');

      return false;
    }

    return true;
  }

  private function socketSetOption(int $option, string $optionText): bool
  {
    \assert(null !== $this->sock);

    return socket_set_option($this->sock, SOL_SOCKET, $option, 1)
      || $this->logError('socket_set_option: failed to set '.$optionText);
  }
}
