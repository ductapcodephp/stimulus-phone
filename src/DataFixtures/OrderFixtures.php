<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\DetailOrder;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class OrderFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('vi_VN');

        $users = $manager->getRepository(User::class)->findAll();
        $products = $manager->getRepository(Product::class)->findAll();

        if (count($users) === 0 || count($products) === 0) {
            throw new \Exception("Cần có sẵn user và product trong CSDL trước.");
        }

        for ($i = 0; $i < 20; $i++) {
            $order = new Order();
            $order->setUser($faker->randomElement($users));
            $order->setName($faker->name());
            $order->setEmail($faker->email());
            $order->setPhone($faker->numerify('09########'));
            $order->setAddress($faker->address());
            $order->setStatus($faker->randomElement(['pending', 'paid', 'shipped', 'cancelled']));
            $createdAt = $faker->dateTimeBetween('-3 months', 'now');
            $order->setCreatedAt($createdAt);

            $manager->persist($order);

            // Tạo 1-3 detailOrder cho mỗi đơn hàng
            $count = rand(1, 3);
            for ($j = 0; $j < $count; $j++) {
                $product = $faker->randomElement($products);
                $quantity = rand(1, 5);
                $price = $product->getPrice(); // hoặc random nếu product chưa có giá
                $total = $quantity * $price;

                $detail = new DetailOrder();
                $detail->setOrder($order);
                $detail->setProduct($product);
                $detail->setQuantity($quantity);
                $detail->setPrice($price);
                $detail->setTotal($total);
                $detail->setCreatedAt($createdAt);

                $manager->persist($detail);
            }
        }

        $manager->flush();
    }
}
