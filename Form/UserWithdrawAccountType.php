<?php

namespace App\Form;

use App\Entity\UserWithdrawAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserWithdrawAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description')
            ->add('name_on_account')
            ->add('iban')
            ->add('bank_name')
            ->add('bic')
            ->add('address')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserWithdrawAccount::class,
            'csrf_protection' => false,
        ]);
    }
}
