<?php

namespace App\Services;

use App\Repository\DetailOrderRepository;

class AdminService
{
    public function calculate(int $selectedMonth, DetailOrderRepository $repo): array
    {
        $getOders = $repo->findAll();
        $monthlyRevenue = array_fill(1, 12, 0);
        $filteredOrders = [];

        foreach ($getOders as $order) {
            $month = (int) $order->getCreatedAt()->format('n');
            $monthlyRevenue[$month] += $order->getTotal();
            if ($selectedMonth === 0 || $month === $selectedMonth) {
                $filteredOrders[] = $order;
            }
        }

        $productStats = [];
        $totalRevenue = 0;

        foreach ($filteredOrders as $order) {
            $product = $order->getProduct();
            $productId = $product->getId();

            if (!isset($productStats[$productId])) {
                $productStats[$productId] = [
                    'product' => $product,
                    'quantity' => 0,
                    'total' => 0,
                    'price' => $order->getPrice(),
                ];
            }

            $productStats[$productId]['quantity'] += $order->getQuantity();
            $productStats[$productId]['total'] += $order->getTotal();
            $totalRevenue += $order->getTotal();
        }

        return [$productStats, $monthlyRevenue, $totalRevenue, $filteredOrders];
    }


}