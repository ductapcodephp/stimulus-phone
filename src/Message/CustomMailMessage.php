<?php

namespace App\Message;

use Symfony\Component\Mime\Email;

class CustomMailMessage
{
     private Email $mail;
     private int $mailID;

     public function __construct(Email $mail, int $mailID)
     {
         $this->mail = $mail;
         $this->mailID = $mailID;
     }

     public function getMailID(): int
     {
         return $this->mailID;
     }
     public function getMail(): Email
     {
         return $this->mail;
     }



}