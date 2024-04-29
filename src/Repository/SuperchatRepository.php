<?php

declare(strict_types=1);

namespace Mati\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mati\Entity\Superchat;

/**
 * @extends ServiceEntityRepository<Superchat>
 *
 * @method null|Superchat find($id, $lockMode = null, $lockVersion = null)
 * @method null|Superchat findOneBy(array $criteria, array $orderBy = null)
 * @method Superchat[]    findAll()
 * @method Superchat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuperchatRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Superchat::class);
  }
}
