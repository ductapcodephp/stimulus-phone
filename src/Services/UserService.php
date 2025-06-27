<?php

namespace App\Services;


use App\Entity\User;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly MailerInterface $mailer
    ) {}

    public function hashPassword(User $user, string $plainPassword): string
    {
        return $this->hasher->hashPassword($user, $plainPassword);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMail(array $toEmails, string $htmlContent): void
    {
        $subject = 'Chào mừng đến với Bình Nguyên Vô Tận';
        foreach ($toEmails as $email) {
            $emailMessage = (new Email())
                ->from('ducps34770@fpt.edu.vn')
                ->to($email)
                ->subject($subject)
                ->html($htmlContent);

            $this->mailer->send($emailMessage);
        }
    }
}