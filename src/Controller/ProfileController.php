<?php

namespace App\Controller;

use App\Entity\DetailOrder;
use App\Entity\Order;
use App\Form\ChangePasswordForm;
use App\Form\PersonalForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    public function __construct(
        private Security $security,
    ){
        $this->security=$security;
    }

    #[Route('/app_profile', name: 'app_profile')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response {
        $user = $this->getUser();
        $userId = $user->getId();

        $personalForm = $this->createForm(PersonalForm::class, $user);
        $personalForm->handleRequest($request);
        if ($personalForm->isSubmitted() && $personalForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Thông tin đã được cập nhật thành công!');
            return $this->redirectToRoute('app_profile');
        }

        $passwordForm = $this->createForm(ChangePasswordForm::class);
        $passwordForm->handleRequest($request);
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $currentPassword = $passwordForm->get('password')->getData();
            if ($passwordHasher->isPasswordValid($user, $currentPassword)) {
                $newPassword = $passwordForm->get('newPassword')->getData();
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();
                $this->addFlash('success', 'Mật khẩu đã được đổi thành công!');
                return $this->redirectToRoute('app_profile');
            } else {
                $this->addFlash('error', 'Mật khẩu hiện tại không đúng!');
            }
        }
        $recentOrders = $entityManager->getRepository(Order::class)->findBy(['user' => $userId], ['id' => 'DESC'], 5);

        return $this->render('phone/infor.html.twig', [
            'personalForm' => $personalForm->createView(),
            'passwordForm' => $passwordForm->createView(),
            'recentOrders' => $recentOrders,
        ]);
    }

    #[Route('/order-history', name: 'app_profile_order_history')]
    public function orderHistory(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $orders = $entityManager->getRepository(Order::class)->findBy(['user' => $user], ['created_at' => 'DESC']);

        return $this->render('phone/order.html.twig', [
            'orders' => $orders,
        ]);
    }
    #[Route('/detail_order_user/{id}', name: 'detail_order_user')]
    public function detail(int $id, EntityManagerInterface $entityManager): Response
    {
        $order = $entityManager->getRepository(Order::class)->find($id);

        if (!$order || $order->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createNotFoundException('Đơn hàng không tồn tại hoặc không thuộc về bạn.');
        }
        $detailOrders = $entityManager->getRepository(DetailOrder::class)->findBy(['order' => $order]);
        $total = 0;
        foreach ($detailOrders as $detail) {
            $total += $detail->getTotal() ?? ($detail->getPrice() * $detail->getQuantity());
        }

        return $this->render('phone/order_Detail.html.twig', [
            'order' => $order,
            'detailOrders' => $detailOrders,
            'total' => $total,
        ]);
    }
}