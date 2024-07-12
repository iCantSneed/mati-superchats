<?php

declare(strict_types=1);

namespace Mati\Ipc;

use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(Terminator::class)]
final class TerminatorIntegrationTest extends KernelTestCase
{
  private const int TEST_IPC_PORT = 64999;

  private LoggerInterface $logger;

  protected function setUp(): void
  {
    self::bootKernel();
    $this->logger = self::getContainer()->get(LoggerInterface::class);
  }

  public function testSendTerminate(): void
  {
    $sender = $this->createTerminator();
    $receiver = $this->createTerminator();

    self::assertTrue($sender->sendTerminate());
    self::assertTrue($receiver->shouldTerminate());
  }

  public function testShouldNotTerminateIfNoEventSent(): void
  {
    $receiver = $this->createTerminator();

    self::assertFalse($receiver->shouldTerminate());
  }

  public function testShouldNotTerminateIfOldEventSent(): void
  {
    $sender = $this->createTerminator();
    $receiver = $this->createTerminator();

    self::assertTrue($sender->sendTerminate());
    $sender->close();
    self::assertFalse($receiver->shouldTerminate());
  }

  private function createTerminator(): Terminator
  {
    return new Terminator($this->logger, self::TEST_IPC_PORT);
  }
}
