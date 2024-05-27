<?php

declare(strict_types=1);

namespace Mati\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mati\Entity\Stream;
use Mati\Entity\Superchat;

class AppFixtures extends Fixture
{
  #[\Override]
  public function load(ObjectManager $manager): void
  {
    $streamDate = new \DateTimeImmutable('2024-01-01T12:00:00-04:00');
    $prevStream = null;

    for ($streamId = 1; $streamId <= 5; ++$streamId) {
      $stream = (new Stream())
        ->setId($streamId)
        ->setPrev($prevStream)
        ->setDate($streamDate)
      ;
      $manager->persist($stream);

      $superchatCreated = $streamDate;
      for ($superchatId = 1; $superchatId <= 10; ++$superchatId) {
        $superchat = (new Superchat())
          ->setId($streamId * 1000 + $superchatId)
          ->setUsername("User {$superchatId}")
          ->setPriceCents($superchatId * 100)
          ->setMessage("Stream {$streamId} superchat {$superchatId}")
          ->setCreated($superchatCreated)
          ->setStream($stream)
        ;
        $manager->persist($superchat);

        $superchatCreated = $superchatCreated->add(new \DateInterval('PT1M'));
      }

      $streamDate = $streamDate->add(new \DateInterval('P1D'));
      $prevStream = $stream;
    }

    $manager->flush();
  }
}
