<?php

declare(strict_types=1);

namespace Mati\Ipc;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final readonly class IpcParameters
{
  public const IPC_ADDRESS = '127.255.255.255';

  public int $port;
  public int $semkey;

  public function __construct(ParameterBagInterface $parameterBag)
  {
    $this->port = $parameterBag->get('mati.ipc.port');
    $this->semkey = $parameterBag->get('mati.ipc.semkey');
  }
}
