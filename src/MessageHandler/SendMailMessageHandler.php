<?php

namespace App\MessageHandler;

use App\Entity\Mail;
use App\Message\CustomMailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class SendMailMessageHandler
{

    private EntityManagerInterface $em;
    private MessageBusInterface $bus;

    public function __construct(EntityManagerInterface $em, MessageBusInterface $bus)
    {
        $this->em = $em;
        $this->bus = $bus;
    }

    public function __invoke(CustomMailMessage $message)
    {
        $getMailId=$message->getMailId();
        $mail=$message->getMail();
        $findMail=$this->em->getRepository(Mail::class)->find($getMailId);
        $this->bus->dispatch(new SendEmailMessage($mail));
        if($findMail){
           $findMail->setStatus('complete');
           $this->em->flush();
        }
    }

}