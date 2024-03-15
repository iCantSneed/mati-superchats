<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\EntityIdentityCollisionException;
use Mati\Entity\Superchat;
use Psr\Log\LoggerInterface;

final class SuperchatResettableRepository
{
  public function __construct(
    private EntityManagerInterface $entityManager,
    private readonly LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  public function save(Superchat $superchat): bool
  {
    try {
      $this->entityManager->persist($superchat);
      $this->entityManager->flush();

      return true;
    } catch (EntityIdentityCollisionException|UniqueConstraintViolationException $e) {
      $this->logger->warning('Superchat already exists', ['exception' => $e]);
      $this->entityManager = new EntityManager($this->entityManager->getConnection(), $this->entityManager->getConfiguration());

      return false;
    }
  }
}
