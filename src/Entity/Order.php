<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    private ?User $user = null;
    #[ORM\OneToMany(targetEntity: Mail::class, mappedBy: 'order')]
    private Collection $mails;
    #[ORM\OneToMany(targetEntity: DetailOrder::class, mappedBy: 'order')]
    private Collection $detailOrders;
    #[ORM\Column(type: 'string')]
    private ?string $phone = null;
    #[ORM\Column(type: 'string')]
    private ?string $email = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }
    public function setMails(Collection $mails): void
    {
        $this->mails = $mails;
    }
    #[ORM\Column(type: 'string')]
    private ?string $status = null;
    #[ORM\Column(type: 'string')]
    private ?string $address = null;
    #[ORM\Column(type: 'string')]
    private ?string $name = null;
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created_at = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getDetailOrders(): Collection
    {
        return $this->detailOrders;
    }

    public function setDetailOrders(Collection $detailOrders): void
    {
        $this->detailOrders = $detailOrders;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function __construct()
    {
        $this->detailOrders = new ArrayCollection();
        $this->mails = new ArrayCollection();
    }
    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function setPhone(?int $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getMails(): Collection
    {
        return $this->mails;
    }


}
