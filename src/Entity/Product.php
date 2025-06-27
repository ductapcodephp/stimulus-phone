<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'product')]
    private Collection $cartItems;
    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: "product")]
    private ?Comment $comments = null;
    #[ORM\OneToMany(targetEntity: DetailOrder::class, mappedBy: 'product')]
    private Collection $detailOrders;

    #[ORM\Column]
    private int $price;

    #[ORM\Column]
    private string $url_img;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string")]
    private string $color;

    #[ORM\Column(type: "integer")]
    private int $inventory;
    #[ORM\Column(type: "integer")]
    private ?int $price_sale = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function setCartItems(Collection $cartItems): void
    {
        $this->cartItems = $cartItems;
    }

    public function getDetailOrders(): Collection
    {
        return $this->detailOrders;
    }

    public function setDetailOrders(Collection $detailOrders): void
    {
        $this->detailOrders = $detailOrders;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function getUrlImg(): string
    {
        return $this->url_img;
    }

    public function setUrlImg(string $url_img): void
    {
        $this->url_img = $url_img;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getInventory(): int
    {
        return $this->inventory;
    }

    public function setInventory(int $inventory): void
    {
        $this->inventory = $inventory;
    }

    public function getPriceSale(): ?int
    {
        return $this->price_sale;
    }

    public function setPriceSale(?int $price_sale): void
    {
        $this->price_sale = $price_sale;
    }

    public function getComments(): ?Comment
    {
        return $this->comments;
    }

    public function setComments(?Comment $comments): void
    {
        $this->comments = $comments;
    }


}