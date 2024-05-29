<?php

declare(strict_types=1);

namespace Mati\Repository\Mixin;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Mati\Entity\Stream;

/**
 * @extends ServiceEntityRepository
 */
trait LatestStreamMixin
{
  private function latestStreamWherePredicate(string $streamTableAlias): string
  {
    $selectStreamWhere = $this->getEntityManager()
      ->createQueryBuilder()
      ->select('MAX(st2)')
      ->from(Stream::class, 'st2')
      ->getDQL()
    ;

    return "{$streamTableAlias}.id = ({$selectStreamWhere})";
  }
}
