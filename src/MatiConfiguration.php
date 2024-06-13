<?php

declare(strict_types=1);

namespace Mati;

final readonly class MatiConfiguration
{
  public const string IPC_ADDRESS = '127.255.255.255';
  public const int IPC_PORT = 65123;

  public const string ENV_ARCHIVE_SECRET = 'ARCHIVE_SECRET';
  public const string ENV_LIVESTREAM_RSS_URL = 'LIVESTREAM_RSS_URL';
}
