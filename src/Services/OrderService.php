<?php
namespace App\Services;

use App\Entity\Order;
use App\Entity\DetailOrder;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createOrder(Order $order, int $userId, array $cartItems): void
    {
        $user = $this->em->getRepository(User::class)->find($userId);
        $order->setUser($user);
        $order->setCreatedAt(new \DateTime());
        $order->setStatus('failed');

        $total = 0;
        foreach ($cartItems as $cartItem) {
            $detailOrder = new DetailOrder();
            $detailOrder->setOrder($order);
            $detailOrder->setProduct($cartItem->getProduct());
            $detailOrder->setQuantity($cartItem->getQuantity());
            $price = $cartItem->getProduct()->getPrice();
            $detailOrder->setPrice($price);
            $subtotal = $price * $cartItem->getQuantity();
            $detailOrder->setTotal($subtotal);
            $detailOrder->setCreatedAt(new \DateTime());
            $this->em->persist($detailOrder);
        }

        $this->em->persist($order);
        $this->em->flush();

    }
}