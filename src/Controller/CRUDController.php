<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Repository\ProductRepository;
use App\Services\CartService;
use App\Services\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class CRUDController extends AbstractController
{
    private $cartService;

    public function __construct(CartService $cartService,


     private Security $security,)
    {
        $this->cartService = $cartService;
        $this->security = $security;

    }

    #[Route('/addToCart/{id}', name: 'addToCart')]
    public function addToCart(int $id, CartService $cartService,ProductRepository $pr): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $product = $pr->find($id);
        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('app_task_index');
        }
        $success = $cartService->addProductToCart($product, $user);

        return $this->redirectToRoute($success ? 'app_task_index' : 'cart');
    }


    #[Route('/remove_cart/{id}', name: 'remove_cart')]
    public function removeCart(int $id, EntityManagerInterface $entityManager): Response
    {
        $cartItem = $entityManager->getRepository(CartItem::class)->find($id);

        if (!$cartItem) {
            $this->addFlash('error','Cart item not found');
        } else {
            $entityManager->remove($cartItem);
            $entityManager->flush();
            $this->addFlash('success', 'Item removed from cart successfully.');
        }

        return $this->redirectToRoute('cart_list');
    }



    #[Route('/cart/increase/{id}', name: 'cart_increase', methods: ['POST'])]
    public function increaseQuantity(CartItem $cartItem, EntityManagerInterface $em): JsonResponse
    {
        $qty = $cartItem->getQuantity() + 1;
        $cartItem->setQuantity($qty);
        $cartItem->setTotal($cartItem->getProduct()->getPrice() * $qty);
        $em->flush();
        $user = $this->getUser();
        $userId=$user->getId();
        $cartData= $this->cartService->getCartData($userId);
        $total=$cartData['total'];
        $countItem=$cartData['countItem'];
        return new JsonResponse(['quantity' => $cartItem->getQuantity(), 'total' => $total,'countItem' => $countItem]);
    }

    #[Route('/cart/decrease/{id}', name: 'cart_decrease', methods: ['POST'])]
    public function decreaseQuantity(CartItem $cartItem, EntityManagerInterface $em): JsonResponse
    {
        $qty = max(1, $cartItem->getQuantity() - 1);
        $cartItem->setQuantity($qty);
        $cartItem->setTotal($cartItem->getProduct()->getPrice() * $qty);
        $em->flush();
        $user = $this->getUser();
        $userId=$user->getId();
        $cartData= $this->cartService->getCartData($userId);
        $total=$cartData['total'];
        $countItem=$cartData['countItem'];
        return new JsonResponse(['quantity' => $qty , 'total' => $total,'countItem' => $countItem]);
    }

}

