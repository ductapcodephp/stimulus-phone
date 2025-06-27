<?php

namespace App\Command;

use App\Entity\DetailOrder;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

#[AsCommand(
    name: 'app:test:create-orders',
    description: 'Tạo 50 đơn hàng test để kiểm tra hệ thống hàng đợi.'
)]
class CreateTestOrdersCommand extends Command

{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerService $mailerService,
        private Environment $twig
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->em->getRepository(User::class)->findOneBy([]);
        if (!$user) {
            $output->writeln('<error>Không tìm thấy user trong hệ thống.</error>');
            return Command::FAILURE;
        }
        $products = $this->em->getRepository(Product::class)->findBy([], null, 5);
        if (count($products) === 0) {
            $output->writeln('<error>Không có sản phẩm nào trong hệ thống.</error>');
            return Command::FAILURE;
        }

        for ($i = 1; $i <= 7; $i++) {
            $order = new Order();
            $order->setUser($user);
            $order->setName("Khách test #$i");
            $order->setPhone('0123456789');
            $order->setEmail('test@example.com');
            $order->setAddress("Địa chỉ test #$i");
            $order->setCreatedAt(new \DateTime());

            $randomCount = rand(1, 3);
            $total = 0;

            for ($j = 0; $j < $randomCount; $j++) {
                $product = $products[array_rand($products)];
                $quantity = rand(1, 5);

                $detail = new DetailOrder();
                $detail->setOrder($order);
                $detail->setProduct($product);
                $detail->setQuantity($quantity);
                $detail->setPrice($product->getPrice());
                $detail->setTotal($product->getPrice() * $quantity);
                $detail->setCreatedAt(new \DateTime());

                $order->getDetailOrders()->add($detail);
                $this->em->persist($detail);

                $total += $detail->getTotal();
            }

            $emailContent = $this->twig->render('email/order_summary.html.twig', [
                'user' => $user,
                'cartItems' => $order->getDetailOrders(),
                'total' => $total,
            ]);
            $this->mailerService->sendEmailMessage([$user->getEmail()], $emailContent);

            $this->em->persist($order);
            $output->writeln("✅ Đã tạo đơn hàng #$i với $randomCount sản phẩm.");
        }

        $this->em->flush();

        $output->writeln('<info>🎉 Đã tạo xong đơn hàng test và gửi email vào queue.</info>');
        return Command::SUCCESS;
    }
}
