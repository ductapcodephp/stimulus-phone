<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'cart')]
    private Collection $Item;
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'carts')]
    private ?User $user = null;

    public function getItem(): Collection
    {
        return $this->Item;
    }

    public function setItem(Collection $Item): void
    {
        $this->Item = $Item;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }




    public function getId(): ?int
    {
        return $this->id;
    }
}
