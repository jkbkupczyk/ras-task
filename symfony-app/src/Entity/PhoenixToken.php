<?php

namespace App\Entity;

use App\Repository\PhoenixTokenRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhoenixTokenRepository::class)]
#[ORM\Table(name: 'phoenix_token')]
class PhoenixToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private int $userId;

    #[ORM\Column(length: 512, nullable: false)]
    private string $token;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $modifyDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getModifyDate(): ?DateTimeImmutable
    {
        return $this->modifyDate;
    }

    public function setModifyDate(DateTimeImmutable $modifyDate): static
    {
        $this->modifyDate = $modifyDate;

        return $this;
    }
}
