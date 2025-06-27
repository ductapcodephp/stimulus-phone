<?php
namespace App\Services;


use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

final class CartService
{
    private EntityManagerInterface $en;

    public function __construct(
        private Security                $security,
        private readonly ManagerRegistry $doctrine,
        EntityManagerInterface           $en,


    )
    {
        $this->en=$en;

    }

    public function getCartData(int $userId): array
    {
        $cart = $this->doctrine->getRepository(Cart::class)->findOneBy(['user' => $userId]);
        $cartItems = [];
        $total = 0;
        $countItem = 0;
        if ($cart) {
            $cartItems = $this->doctrine->getRepository(CartItem::class)->findBy(['cart' => $cart->getId()]);
            foreach ($cartItems as $cartItem) {
                $total += $cartItem->getProduct()->getPrice() * $cartItem->getQuantity();
                $countItem += $cartItem->getQuantity();
            }
        }

        return [
            'cart' => $cart,
            'cartItems' => $cartItems,
            'total' => $total,
            'countItem' => $countItem,
        ];
    }
    public function addProductToCart(Product $product,$user): bool
    {
        if (!$product) {
            return false;
        }

        $cart = $this->en->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->en->persist($cart);
        }

        $cartItem = $this->en->getRepository(CartItem::class)->findOneBy([
            'cart' => $cart,
            'product' => $product,
        ]);

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity(1);
            $this->en->persist($cartItem);
        }

        $this->en->flush();

        return true;
    }

}
