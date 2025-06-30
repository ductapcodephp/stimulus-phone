<?php

namespace App\Controller;
use App\Entity\Cart;
use App\Entity\Comment;
use App\Entity\DetailOrder;
use App\Entity\Mail;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Form\CommentForm;
use App\Form\OrderForm;
use App\Form\RegisterTypeForm;
use App\Message\PaymentCompletedMessage;
use App\Services\CartService;
use App\Services\MailerService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ProductService;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final class TaskController extends AbstractController
{
    private CartService $cartService;
    private OrderService $orderService;
    private MailerService $mailerService;
    private Environment $twig;
    private ProductService $productService;
    private Security $security;
    private UserService $userService;

    private PaymentService $paymentService;

    public function __construct(
        CartService $cartService,
        OrderService $orderService,
        MailerService $mailerService,
        Environment $twig,
        ProductService $productService,
        Security $security,
        UserService $userService,

        PaymentService $paymentService
    ) {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->mailerService = $mailerService;
        $this->twig = $twig;
        $this->productService = $productService;
        $this->security = $security;
        $this->userService = $userService;
        $this->paymentService = $paymentService;
    }
    #[Route('/', name: 'app_task_index')]
    public function index(EntityManagerInterface $doctrine): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin');
        }
        $products = $doctrine->getRepository(Product::class)->findBy([], ['id' => 'DESC'], 3);
        return $this->render('/phone/index.html.twig', [
            'products' => $products,
        ]);
    }


    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $em, MailerInterface $mailer, MessageBusInterface $bus): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterTypeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
               $this->addFlash('erorr','email exist');
            } else {
                $hashedPassword = $this->userService->hashPassword($user, $user->getPassword());
                $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_USER']);
                $em->persist($user);
                $em->flush();

                $emailContent = $this->render('email/welcome.html.twig', [
                    'user' => $user,
                ]);
                $this->userService->sendMail([$user->getEmail()], $emailContent);

                return $this->redirectToRoute('app_task_index');
            }
        }

        return $this->render('home/register.html.twig', [
            'registerForm' => $form->createView(),
        ]);
    }

    #[Route('/product_detail/{id}', name: 'product_detail')]
    public function detail(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        $user = $this->getUser();

        if (!$product) {
            $this->addFlash('erorr','Sản phẩm không tồn tại');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentForm::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setProduct($product);
            $comment->setAuthor($user);
            $comment->setCreatedAt(new \DateTimeImmutable());

            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Cảm ơn bạn đã bình luận!');
            return $this->redirectToRoute('product_detail', ['id' => $id]);
        }

        $getComments = $em->getRepository(Comment::class)->findBy(['product' => $id]);
        return $this->render('phone/detail_item.html.twig', [
            'comments' => $getComments,
            'product' => $product,
            'commentForm' => $form->createView()
        ]);
    }


    #[Route('/cart', name: 'cart_list')]
    public function cart(Request $request, ManagerRegistry $doctrine): Response
    {

        $user= $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $userId =$user->getId();

        $cartData = $this->cartService->getCartData($userId);

        if (empty($cartData['cartItems'])) {
            $this->addFlash('info', 'Cart is empty.');
        }

        return $this->render('phone/cart.html.twig', [
            'cartItems' => $cartData['cartItems'],
            'total' => $cartData['total'],
            'cartCount' => $cartData['countItem'],
        ]);
    }


    #[Route('/product', name: 'product')]
    public function product(Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search');

        $repository = $em->getRepository(Product::class);

        if ($search) {
            $products = $repository->createQueryBuilder('p')
                ->where('p.name LIKE :search')
                ->setParameter('search', '%' . strtolower($search) . '%')
                ->getQuery()
                ->getResult();
        } else {
            $products = $repository->findAll();
        }

        return $this->render('phone/product.html.twig', [
            'products' => $products,
        ]);
    }
    #[Route('contact', name: 'contact')]
    public function contact(EntityManagerInterface $em): Response
    {
        return $this->render('phone/contact.html.twig');
    }
    #[Route('/about', name: 'about')]
    public function about(): Response{
        return $this->render('phone/about.html.twig');
    }
    #[Route('/checkout', name: 'checkout')]
    public function checkout(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Please login to continue.');
            return $this->redirectToRoute('app_login');
        }
        $cartRepository = $em->getRepository(Cart::class);
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart || $cart->getItem()->isEmpty()) {
            $this->addFlash('error', 'Cart is empty.');
            return $this->redirectToRoute('cart_list');
        }

        $cartItems = $cart->getItem()->toArray();
        $total = array_reduce($cartItems, function ($carry, $item) {
            return $carry + ($item->getProduct()->getPrice() * $item->getQuantity());
        }, 0);

        $cartData = [
            'cartItems' => $cartItems,
            'total' => $total,
        ];

        $order = new Order();
        $form = $this->createForm(OrderForm::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->orderService->createOrder($order, $user->getId(), $cartData['cartItems']);
            $em->flush();

            $paymentUrl = $this->paymentService->createPaymentUrl([
                'order_id' => $order->getId(),
                'amount' => $cartData['total'],
                'description' => 'Thanh toán đơn hàng #' . $order->getId(),
            ]);

            return $this->redirect($paymentUrl);
        }

        return $this->render('phone/checkout.html.twig', [
            'form' => $form->createView(),
            'cartItems' => $cartData['cartItems'],
            'total' => $cartData['total'],
        ]);
    }

    #[Route('/payment/vnpay/return', name: 'vnpay_return')]
    public function vnpayReturn(Request $request, EntityManagerInterface $em,MessageBusInterface $bus): Response
    {
        $inputData = $request->query->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? null;
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

        ksort($inputData);
        $hashData = http_build_query($inputData);
        $secureHash = hash_hmac('sha512', $hashData, $_ENV['VNPAY_HASH_SECRET']);

        if ($secureHash === $vnp_SecureHash && $inputData['vnp_ResponseCode'] === '00') {
            $orderId = $inputData['vnp_TxnRef'];
            $order = $em->getRepository(Order::class)->find($orderId);

            if (!$order) {
                $this->addFlash('error', 'Không tìm thấy đơn hàng.');
                return $this->redirectToRoute('cart_list');
            }

            if ($order->getStatus() !== 'paid') {
                $order->setStatus('paid');
                $em->flush();
                $user = $this->security->getUser();
                $bus->dispatch(new PaymentCompletedMessage($order->getId(), $user->getId()));

                $this->addFlash('success', 'Thanh toán thành công! Đơn hàng đang được xử lý.');
                return $this->redirectToRoute('app_task_index');
            }
        }

        $this->addFlash('error', 'Thanh toán thất bại hoặc bị hủy.');
        return $this->redirectToRoute('cart_list');
    }
    #[Route('qtyCart', name: 'qtyCart')]
    public function qtyCart(): Response
    {
        $user=$this->security->getUser();
        $cartData=$this->cartService->getCartData($user->getId());
        $cartItems = $cartData['countItem'];
        return new JsonResponse(['countItem' => $cartItems]);

    }

}