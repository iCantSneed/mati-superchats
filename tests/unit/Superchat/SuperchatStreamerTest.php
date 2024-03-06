<?php

declare(strict_types=1);

namespace Mati\Tests\Unit\Superchat;

use Mati\Ipc\IpcClientInterface;
use Mati\Superchat\FlusherInterface;
use Mati\Superchat\SuperchatStreamer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SuperchatStreamer::class)]
final class SuperchatStreamerTest extends TestCase
{
  private IpcClientInterface&MockObject $mockIpcClient;
  private SuperchatStreamer $superchatStreamer;

  protected function setUp(): void
  {
    $this->mockIpcClient = $this->createMock(IpcClientInterface::class);
    $mockFlusher = $this->createStub(FlusherInterface::class);
    $this->superchatStreamer = new SuperchatStreamer($this->mockIpcClient, $mockFlusher);
  }

  public function testStreamEvent(): void
  {
    $this->mockIpcClient->expects(self::once())->method('init')->willReturn(true);
    $this->mockIpcClient->expects(self::once())->method('receive')->willReturn(['my event']);

    $expectedOutput = "\ndata: my event\n\n";

    $this->expectOutputString($expectedOutput);
    $this->superchatStreamer->streamEvents();
  }

  public function testStreamMultilineEvent(): void
  {
    $this->mockIpcClient->expects(self::once())->method('init')->willReturn(true);
    $this->mockIpcClient->expects(self::once())->method('receive')->willReturn(["my\nevent\n"]);

    $expectedOutput = "\ndata: my\ndata: event\ndata: \n\n";

    $this->expectOutputString($expectedOutput);
    $this->superchatStreamer->streamEvents();
  }

  public function testIpcClientFailureReturnsImmediately(): void
  {
    $this->mockIpcClient->expects(self::once())->method('init')->willReturn(false);
    $this->mockIpcClient->expects(self::never())->method('receive');

    $this->expectOutputString('');
    $this->superchatStreamer->streamEvents();
  }
}
