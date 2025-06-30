<?php

namespace App\Message;

use Symfony\Component\Mime\Email;

class CustomMailMessage
{
     private int $orderId;
     private int $mailID;
    private Email $mail;
     public function __construct(Email $mail,$orderId, int $mailID)
     {
         $this->orderId = $orderId;
         $this->mailID = $mailID;
         $this->mail = $mail;
     }

     public function getMailID(): int
     {
         return $this->mailID;
     }

     public function getOrderID(): int
     {
         return $this->orderId;
     }
    public function getMail(): Email
    {
        return $this->mail;
    }       
}