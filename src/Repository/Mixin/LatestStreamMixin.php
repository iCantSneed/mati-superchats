<?php

declare(strict_types=1);

namespace Mati\Repository\Mixin;

use Doctrine\ORM\EntityManagerInterface;
use Mati\Entity\Stream;

trait LatestStreamMixin
{
  abstract protected function getEntityManager(): EntityManagerInterface;

  private function latestStreamWherePredicate(string $streamTableAlias): string
  {
    $selectStreamWhere = $this->getEntityManager()
      ->createQueryBuilder()
      ->select('st2')
      ->from(Stream::class, 'st2')
      ->select('MAX(st2)')
      ->getDQL()
    ;

    return "{$streamTableAlias}.id = ({$selectStreamWhere})";
  }
}
