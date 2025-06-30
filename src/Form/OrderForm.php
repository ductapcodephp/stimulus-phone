<?php

namespace App\Form;

use App\Entity\Order;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
class OrderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Họ và tên',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the recipient name.']),
                    new Assert\Type(['type' => 'string']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your email.']),
                    new Email(['message' => 'Email not valid.']),
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Số điện thoại',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your phone number.']),
                    new Assert\Type(['type' => 'numeric']),
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Địa chỉ',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your address.']),
                    new Assert\Type(['type' => 'string']),
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
