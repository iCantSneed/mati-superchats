<?php

declare(strict_types=1);

namespace Mati\Ipc;

use Mati\MatiConfiguration;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class Terminator
{
  private const int QUEUE_MESSAGE_TYPE = 0x364793B1;
  private const string QUEUE_TERMINATE_MESSAGE = '2dgfh4w80480sesdh3rfisdfdfs389f8';
  private const int QUEUE_TERMINATE_MESSAGE_SIZE = 32;

  private \SysvMessageQueue $messageQueue;

  public function __construct(
    private LoggerInterface $logger,
    #[Autowire(env: MatiConfiguration::ENV_IPC_PORT)]
    int $ipcPort
  ) {
    $messageQueue = msg_get_queue($ipcPort);
    if (false === $messageQueue) {
      $this->logger->critical('Terminator: failed to create message queue');

      throw new \Exception();
    }
    $this->messageQueue = $messageQueue;
  }

  public function shouldTerminate(): bool
  {
    $result = msg_receive(
      $this->messageQueue,
      self::QUEUE_MESSAGE_TYPE,
      $receivedMessageType,
      self::QUEUE_TERMINATE_MESSAGE_SIZE,
      $message,
      false,
      MSG_IPC_NOWAIT,
      $errno
    );
    $this->logger->debug('Terminator: received message', [
      'result' => $result,
      'receivedMessageType' => $receivedMessageType,
      'message' => $message,
      'errno' => $errno,
    ]);

    return $result && self::QUEUE_MESSAGE_TYPE === $receivedMessageType && self::QUEUE_TERMINATE_MESSAGE === $message;
  }

  public function sendTerminate(): bool
  {
    return msg_send($this->messageQueue, self::QUEUE_MESSAGE_TYPE, self::QUEUE_TERMINATE_MESSAGE, false);
  }

  /**
   * @internal
   */
  public function close(): void
  {
    msg_remove_queue($this->messageQueue);
  }
}
