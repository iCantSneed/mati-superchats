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

    $lastStreams = $this->getEntityManager()
      ->createQuery('SELECT st FROM Mati\Entity\Stream st WHERE st.id=(SELECT MAX(st2.id) FROM Mati\Entity\Stream st2)')
      ->getResult()
    ;
    \assert(\is_array($lastStreams) && 1 === \count($lastStreams));
    [$lastStream] = $lastStreams;
    \assert($lastStream instanceof Stream);

    $stream = (new Stream())
      ->setId($id)
      ->setPrev($lastStream)
      ->setDate($now)
    ;
    $this->getEntityManager()->persist($stream);

    return $stream;
  }
}
