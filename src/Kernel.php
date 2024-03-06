<?php

declare(strict_types=1);

namespace Mati;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
  use MicroKernelTrait {
    configureContainer as microKernelConfigureContainer;
  }

  public function __construct(string $environment, bool $debug, private bool $isConsole = false)
  {
    parent::__construct($environment, $debug);
  }

  protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
  {
    $this->microKernelConfigureContainer($container, $loader, $builder);

    if ($this->isConsole) {
      $configDir = $this->getConfigDir();

      $container->import($configDir.'/{packages}/console/*.{php,yaml}');
    }
  }
}
