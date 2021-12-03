<?php

namespace App\Form;

use App\Entity\MultinodeRequestUpdateItemResults;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultinodeRequestUpdateItemResultsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_id', TextType::class, ['required' => false])
            ->add('chain')
            ->add('updateState')
            ->add('user')
            ->add('category')
            ->add('address')
            ->add('addressTo')
            ->add('addressFrom')
            ->add('txid')
            ->add('fee', TextType::class)
            ->add('amount')
            ->add('confirmations')
            ->add('state', IntegerType::class, ['required' => false])
            ->add('createdAt', TextType::class, ['mapped' => false])
            ->add('confirmedAt', TextType::class, ['mapped' => false])
            ->add('cryptoBrokerId', TextType::class, ['required' => false])
            ->add('coinbaseTxid', TextType::class, ['required' => false])
            ->add('__v', TextType::class, ['required' => false])
            ->add('txDbId')
            ->add('isCoinbaseFor')
            ->add('domainName')
            ->add('receiveFee', TextType::class, ['required' => false])
            ->add('receiveFeeCurrency', TextType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MultinodeRequestUpdateItemResults::class,
            'csrf_protection' => false,
        ]);
    }
}
