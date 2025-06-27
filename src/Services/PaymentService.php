<?php

namespace App\Services;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentService
{
    private string $vnpUrl;
    private string $vnpTmnCode;
    private string $vnpHashSecret;
    private UrlGeneratorInterface $router;

    public function __construct(string $vnpUrl, string $vnpTmnCode, string $vnpHashSecret, UrlGeneratorInterface $router)
    {
        $this->vnpUrl = $vnpUrl;
        $this->vnpTmnCode = $vnpTmnCode;
        $this->vnpHashSecret = $vnpHashSecret;
        $this->router = $router;
    }

    public function createPaymentUrl(array $orderInfo): string
    {
        $vnp_TxnRef = $orderInfo['order_id'];
        $vnp_OrderInfo = $orderInfo['description'];
        $vnp_Amount = $orderInfo['amount'] * 100;
        $vnp_ReturnUrl = $this->router->generate('vnpay_return', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnpTmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $_SERVER['REMOTE_ADDR'],
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        ksort($inputData);
        $query = http_build_query($inputData);
        $vnp_SecureHash = hash_hmac('sha512', $query, $this->vnpHashSecret);

        return $this->vnpUrl . "?" . $query . '&vnp_SecureHash=' . $vnp_SecureHash;
    }
}