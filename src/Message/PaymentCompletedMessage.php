<?php

namespace App\Message;

class PaymentCompletedMessage
{
    private int $orderId;
    private int $userId;

    public function __construct(int $orderId, int $userId)
    {
        $this->orderId = $orderId;
        $this->userId = $userId;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
