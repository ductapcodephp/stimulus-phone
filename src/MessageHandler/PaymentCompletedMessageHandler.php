<?php

namespace App\MessageHandler;

use App\Message\PaymentCompletedMessage;
use App\Services\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Entity\Order;
use App\Entity\Mail;
use App\Entity\Product;
use App\Entity\DetailOrder;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Message\CustomMailMessage;
use Twig\Environment;

#[AsMessageHandler]
class PaymentCompletedMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private CartService $cartService,
        private MessageBusInterface $bus,
        private Environment $twig
    ) {}

    public function __invoke(PaymentCompletedMessage $message): void
    {
        $order = $this->em->getRepository(Order::class)->find($message->getOrderId());

        if (!$order || $order->getStatus() !== 'paid') {
            return;
        }

        $cartData = $this->cartService->getCartData($message->getUserId());
        $cartItems = $cartData['cartItems'];
        foreach ($cartItems as $item) {
            $product = $this->em->getRepository(Product::class)->find($item->getProduct());
            if (!$product || $product->getInventory() < $item->getQuantity()) {
                $order->setStatus('failed');
                $this->em->flush();
                return;
            }
        }
        foreach ($cartItems as $item) {
            $product = $this->em->getRepository(Product::class)->find($item->getProduct());
            if ($product) {
                $product->setInventory($product->getInventory() - $item->getQuantity());
            }
            $this->em->remove($item);
        }

        $order->setStatus('complete');
        $this->em->flush();
        $detailOrder = $this->em->getRepository(DetailOrder::class)->findOneBy(['order' => $order->getId()]);
        $total = $detailOrder?->getTotal() ?? 0;

        $emailContent = $this->twig->render('email/order_summary.html.twig', [
            'user' => $order->getUser(),
            'detailOrder' => $detailOrder,
            'total' => $total,
        ]);

        $mail = new Mail();
        $mail->setStatus('sent');
        $mail->setJsonData([
            'subject' => 'Xác nhận đơn hàng',
            'recipient' => $order->getEmail(),
            'body' => $emailContent,
        ]);

        $this->em->persist($mail);
        $this->em->flush();

        $email = (new Email())
            ->from(new Address('shop@example.com', 'Tech Phone'))
            ->to($order->getEmail())
            ->subject('Xác nhận đơn hàng #' . $order->getId())
            ->html($emailContent);

        $this->bus->dispatch(new CustomMailMessage($email, $order->getId(), $mail->getId()));
    }
}
