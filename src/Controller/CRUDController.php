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
use Symfony\Component\HttpFoundation\Request;
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


    #[Route('/addToCart/{id}', name: 'addToCart', methods: ['POST'])]
    public function addToCart(int $id,Request $request, CartService $cartService, ProductRepository $productRepository): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Bạn cần đăng nhập'], 401);
        }

        $product = $productRepository->find($id);
        if (!$product) {
            return new JsonResponse(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $quantity = isset($data['quantity']) ? (int) $data['quantity'] : 1;
        if ($quantity < 1) {
            return new JsonResponse(['message' => 'Số lượng không hợp lệ'], 400);
        }

        $cartService->addProductToCart($product, $user, $quantity);

        return new JsonResponse(['message' => 'Đã thêm sản phẩm vào giỏ', 'quantity' => $quantity]);
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
        $cartData = $this->cartService->getCartData($user->getId());
        $total = $cartData['total'];

        return new JsonResponse([
            'quantity' => $qty,
            'total' => $total
        ]);
    }

    #[Route('/cart/decrease/{id}', name: 'cart_decrease', methods: ['POST'])]
    public function decreaseQuantity(CartItem $cartItem, EntityManagerInterface $em): JsonResponse
    {
        $qty = max(1, $cartItem->getQuantity() - 1);
        $cartItem->setQuantity($qty);
        $cartItem->setTotal($cartItem->getProduct()->getPrice() * $qty);
        $em->flush();

        $user = $this->getUser();
        $cartData = $this->cartService->getCartData($user->getId());
        $total = $cartData['total'];

        return new JsonResponse([
            'quantity' => $qty,
            'total' => $total
        ]);
    }


}

