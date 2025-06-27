<?php

namespace App\Services;


use App\Message\CustomMailMessage;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }


    public function sendEmailMessage(string $content,$mailId,$mailUser): void
    {


            $email = (new Email())
                ->from(new Address('ducps34770@fpt.edu.vn', 'Tech Phone'))
                ->to($mailUser)
                ->subject('Xác nhận đơn hàng')
                ->html($content);
            $this->bus->dispatch(new CustomMailMessage($email,$mailId));

    }
}