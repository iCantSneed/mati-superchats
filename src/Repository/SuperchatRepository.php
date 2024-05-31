<?php

declare(strict_types=1);

namespace Mati\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Mati\Entity\Superchat;
use Mati\Repository\Mixin\LatestStreamMixin;

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
  use LatestStreamMixin;

  /**
   * TODO https://github.com/vimeo/psalm/issues/9216.
   *
   * @psalm-suppress PossiblyUnusedParam
   */
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Superchat::class);
  }

  /**
   * TODO https://github.com/vimeo/psalm/issues/9216.
   *
   * @psalm-suppress PossiblyUnusedParam
   */
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
   * TODO https://github.com/vimeo/psalm/issues/9216.
   *
   * @psalm-suppress PossiblyUnusedParam
   *
   * @return non-empty-list<Superchat>
   */
  public function findLatest(): array
  {
    $superchats = $this->createQueryBuilder('su')
      ->select('su', 'st')
      ->innerJoin('su.stream', 'st', Expr\Join::WITH, $this->latestStreamWherePredicate('st'))
      ->getQuery()
      ->getResult()
    ;
    \assert(!empty($superchats));

    return $superchats;
  }

  /**
   * TODO https://github.com/vimeo/psalm/issues/9216.
   *
   * @psalm-suppress PossiblyUnusedParam
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
