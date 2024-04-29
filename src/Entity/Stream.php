<?php

declare(strict_types=1);

namespace Mati\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mati\Repository\StreamRepository;

#[ORM\Entity(repositoryClass: StreamRepository::class)]
class Stream
{
  #[ORM\Id]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\OneToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
  private ?self $prev = null;

  #[ORM\Column(type: Types::DATE_IMMUTABLE)]
  private ?\DateTimeImmutable $date = null;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): static
  {
    $this->id = $id;

    return $this;
  }

  public function getPrev(): ?self
  {
    return $this->prev;
  }

  public function setPrev(?self $prev): static
  {
    $this->prev = $prev;

    return $this;
  }

  public function getDate(): ?\DateTimeImmutable
  {
    return $this->date;
  }

  public function setDate(\DateTimeImmutable $date): static
  {
    $this->date = $date;

    return $this;
  }
}
