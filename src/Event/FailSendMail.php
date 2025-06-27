<?php
namespace App\Event;

use App\Entity\Mail;
use App\Message\CustomMailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\FailedMessageEvent;

class FailSendMail implements EventSubscriberInterface
{

    private EntityManagerInterface $em;

    public static function getSubscribedEvents(): array
    {
        return [
            FailedMessageEvent::class => 'onFailedMessage',
        ];
    }

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function onFailedMessage(FailedMessageEvent $event): void{
        try {
            $envelope = $event->getEnvelope();
            $message = $envelope->getMessage();
            if($message instanceof CustomMailMessage) {
                $mailID=$message->getMailID();
                $mail=$this->em->getRepository(Mail::class)->find($mailID);
                if($mail){
                $mail->setStatus('failed');
                $this->em->flush();
                }
            }
        }
        catch (\Throwable $throwable){
            $message = $throwable->getMessage();
        }
    }
}