<?php

declare(strict_types=1);

namespace Mati\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mati\Entity\Stream;

/**
 * @extends ServiceEntityRepository<Stream>
 *
 * @method null|Stream find($id, $lockMode = null, $lockVersion = null)
 * @method null|Stream findOneBy(array $criteria, array $orderBy = null)
 * @method Stream[]    findAll()
 * @method Stream[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StreamRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Stream::class);
  }

  public function getOrCreateStream(int $id, \DateTimeImmutable $now): Stream
  {
    $stream = $this->find($id);
    if (null !== $stream) {
      return $stream;
    }

    $qb = $this->createQueryBuilder('st');
    $lastStream = $qb
      ->where($qb->select($qb->expr()->max('id')))
      ->getQuery()
      ->getSingleResult()
    ;
    \assert($lastStream instanceof Stream);

    $stream = (new Stream())
      ->setId($id)
      ->setPrev($lastStream)
      ->setDate($now)
    ;
    $this->getEntityManager()->persist($stream);
    $this->getEntityManager()->flush();

    return $stream;
  }
}
