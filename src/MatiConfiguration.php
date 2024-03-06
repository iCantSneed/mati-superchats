<?php

declare(strict_types=1);

namespace Mati;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final readonly class MatiConfiguration
{
  public const IPC_ADDRESS = '127.255.255.255';

  public string $livestreamLandingUrl;
  public int $ipcPort;
  public int $ipcSemkey;

  public function __construct(ParameterBagInterface $parameterBag)
  {
    $this->livestreamLandingUrl = $parameterBag->get('mati.rumble.livestreams');
    $this->ipcPort = $parameterBag->get('mati.ipc.port');
    $this->ipcSemkey = $parameterBag->get('mati.ipc.semkey');
  }
}
