<?php

declare(strict_types=1);

namespace Mati\Ipc;

use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(IpcClient::class)]
#[CoversClass(IpcServer::class)]
final class IpcIntegrationTest extends KernelTestCase
{
  private const int TEST_IPC_PORT = 64999;

  private LoggerInterface $logger;

  protected function setUp(): void
  {
    self::bootKernel();
    $this->logger = self::getContainer()->get(LoggerInterface::class);
  }

  public function testSendToSingleClient(): void
  {
    $server = $this->createServer();
    $client = $this->createClient();

    $message = 'TEST MESSAGE FOR SINGLE CLIENT';
    $server->send($message);
    $receivedMessage = $client->receive()->current();
    self::assertSame($message, $receivedMessage);
  }

  public function testBroadcastToMultipleClients(): void
  {
    $server = $this->createServer();
    $clients = [$this->createClient(), $this->createClient(), $this->createClient()];

    $message = 'TEST MESSAGE FOR MULTIPLE CLIENTS';
    $server->send($message);
    foreach ($clients as $id => $client) {
      $receivedMessage = $client->receive()->current();
      self::assertSame($message, $receivedMessage, "Failure on client {$id}");
    }
  }

  public function testListeningToUnopenedConnection(): void
  {
    $client = $this->createClient();
    $receivedMessage = $client->receive()->current();
    self::assertNull($receivedMessage);
  }

  private function createServer(): IpcServer
  {
    $server = new IpcServer($this->logger, self::TEST_IPC_PORT);
    self::assertTrue($server->init());

    return $server;
  }

  private function createClient(): IpcClient
  {
    $client = new IpcClient($this->logger, self::TEST_IPC_PORT);
    self::assertTrue($client->init(1));

    return $client;
  }
}
