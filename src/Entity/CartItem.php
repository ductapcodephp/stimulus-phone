<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'cartItem')]
    private ?Product $product = null;
    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'Item')]
    private ?Cart $cart = null;
    #[ORM\Column(type: 'float')]
    private ?float $total = 0.0;
    #[ORM\Column(type: 'integer')]
    private ?int $quantity = 0;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }


    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }
    public function setProduct(?Product $product): void
    {
        $this->product = $product;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): void
    {
        $this->total = $total;
    }


    /**
     * @return Cart|null
     */
    public function getCart(): ?Cart
    {
        return $this->cart;
    }
    public function setCart(?Cart $cart): void
    {
        $this->cart = $cart;
    }

}
