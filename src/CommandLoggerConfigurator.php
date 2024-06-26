<?php

declare(strict_types=1);

namespace Mati;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

final readonly class CommandLoggerConfigurator
{
  private bool $overrideLogger;

  public function __construct(
    private LoggerInterface $clionlyLogger,
  ) {
    $this->overrideLogger = \PHP_SAPI === 'cli';
  }

  public function configure(Logger $logger): void
  {
    if ($this->overrideLogger) {
      \assert($this->clionlyLogger instanceof Logger);
      $logger->setHandlers($this->clionlyLogger->getHandlers());
    }
  }
}
