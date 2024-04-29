<?php

declare(strict_types=1);

namespace Mati\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mati\Repository\SuperchatRepository;

#[ORM\Entity(repositoryClass: SuperchatRepository::class)]
class Superchat
{
  #[ORM\Id]
  #[ORM\Column(type: Types::BIGINT)]
  private ?int $id = null;

  #[ORM\Column(type: Types::STRING)]
  private ?string $username = null;

  #[ORM\Column(type: Types::SMALLINT)]
  private ?int $price_cents = null;

  #[ORM\Column(type: Types::TEXT)]
  private ?string $message = null;

  #[ORM\Column]
  private ?\DateTimeImmutable $created = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?Stream $stream = null;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): static
  {
    $this->id = $id;

    return $this;
  }

  public function getUsername(): ?string
  {
    return $this->username;
  }

  public function setUsername(string $username): static
  {
    $this->username = $username;

    return $this;
  }

  public function getPriceCents(): ?int
  {
    return $this->price_cents;
  }

  public function setPriceCents(int $price_cents): static
  {
    $this->price_cents = $price_cents;

    return $this;
  }

  public function getMessage(): ?string
  {
    return $this->message;
  }

  public function setMessage(string $message): static
  {
    $this->message = $message;

    return $this;
  }

  public function getCreated(): ?\DateTimeImmutable
  {
    return $this->created;
  }

  public function setCreated(\DateTimeImmutable $created): static
  {
    $this->created = $created;

    return $this;
  }

  public function getStream(): ?Stream
  {
    return $this->stream;
  }

  public function setStream(?Stream $stream): static
  {
    $this->stream = $stream;

    return $this;
  }
}
