<?php

declare(strict_types=1);

namespace Mati\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
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

  public function persistIfNew(Superchat $superchat): bool
  {
    $existingSuperchat = $this->find($superchat->getId());
    if (null !== $existingSuperchat) {
      return false;
    }

    $this->getEntityManager()->persist($superchat);

    return true;
  }

  /**
   * @return Superchat[]
   */
  public function findByDate(\DateTimeImmutable $date): array
  {
    $qb = $this->createQueryBuilder('su');

    return $qb
      ->innerJoin('su.stream', 'st', Expr\Join::WITH, $qb->expr()->eq('st.date', '?1'))
      ->setParameter(1, $date)
      ->getQuery()
      ->getResult()
    ;
  }
}
