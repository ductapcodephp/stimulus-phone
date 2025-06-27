<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProducFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $baseProducts = [
            ['name' => 'iPhone 15', 'url_img' => '/img/shopping.webp', 'color' => 'pink'],
            ['name' => 'Samsung Galaxy S24', 'url_img' => '/img/galaxy.webp', 'color' => 'blue'],
            ['name' => 'Xiaomi 14 Ultra', 'url_img' => '/img/xioumi.webp', 'color' => 'gray'],
        ];

        for ($i = 1; $i <= 20; $i++) {
            $base = $baseProducts[array_rand($baseProducts)];

            $product = new Product();
            $product->setName($base['name'] . ' - Phiên bản ' . $i);
            $product->setPrice(rand(15000000, 30000000));
            $product->setUrlImg($base['url_img']);
            $product->setDescription('Sản phẩm chính hãng, bảo hành 12 tháng');
            $product->setColor($base['color']);
            $product->setInventory(rand(5, 100));
            $product->setPriceSale(rand(1000000, 5000000));

            $manager->persist($product);
        }

        $manager->flush();
    }
}
