<?php

declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\UX\StimulusBundle\StimulusBundle;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

return [
  FrameworkBundle::class => ['all' => true],
  DebugBundle::class => ['dev' => true],
  TwigBundle::class => ['all' => true],
  WebProfilerBundle::class => ['dev' => true, 'test' => true],
  MakerBundle::class => ['dev' => true],
  MonologBundle::class => ['all' => true],
  DoctrineBundle::class => ['all' => true],
  DoctrineMigrationsBundle::class => ['all' => true],
  DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
  TwigExtraBundle::class => ['all' => true],
  StimulusBundle::class => ['all' => true],
  TurboBundle::class => ['all' => true],
  SecurityBundle::class => ['all' => true],
];
