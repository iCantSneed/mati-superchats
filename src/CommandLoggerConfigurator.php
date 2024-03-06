<?php

declare(strict_types=1);

namespace Mati;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class CommandLoggerConfigurator
{
  private bool $overrideLogger;

  public function __construct(
    #[Autowire(service: 'monolog.handler.command')]
    private HandlerInterface $commandHandler
  ) {
    $this->overrideLogger = \PHP_SAPI === 'cli';
  }

  public function configure(Logger $logger): void
  {
    if ($this->overrideLogger) {
      $logger->setHandlers([$this->commandHandler]);
    }
  }
}
