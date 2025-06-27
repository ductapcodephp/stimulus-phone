<?php
namespace App\Services;


use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

final class ProductService
{
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em,
        private readonly string $imgDirectory
    ) {
        $this->em = $em;
    }

    public function deleteProduct(Product $product): void
    {
        $imgPath = $product->getUrlImg();
        if ($imgPath) {
            $fullPath = $this->imgDirectory . '/' . $imgPath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        $this->em->remove($product);
        $this->em->flush();
    }
}
