<?php

namespace App\Entity;

use App\Repository\MailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailRepository::class)]
class Mail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $jsonData = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getJsonData(): ?array
    {
        return $this->jsonData;
    }

    public function setJsonData(?array $jsonData): void
    {
        $this->jsonData = $jsonData;
    }

    public function getSubject(): ?string
    {
        return $this->jsonData['subject'] ?? null;
    }

    public function getRecipient(): ?string
    {
        return $this->jsonData['recipient'] ?? null;
    }

    public function getBody(): ?string
    {
        return $this->jsonData['body'] ?? null;
    }

    public function getFile(): ?string
    {
        return $this->jsonData['file'] ?? null;
    }
}
