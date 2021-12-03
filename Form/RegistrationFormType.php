<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('phone') // todo: remove
            ->add('hash', HiddenType::class, [
                'mapped' => false
            ])
            ->add('plain_password', RepeatedType::class, [
                'type' => PasswordType::class,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'invalid_message' => 'The password fields must match.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('timezone')
            ->add('two_factor_email')
            ->add('two_factor_google')
            ->add('first_name')
            ->add('last_name')

            ->add('gender')
            ->add('card_name')
            ->add('birth_country')
            ->add('birth_date')
            ->add('birth_place')

            ->add('residence_apartment')
            ->add('residence_city')
            ->add('residence_country')
            ->add('residence_identification_number')
            ->add('residence_house')
            ->add('residence_province')
            ->add('residence_street')
            ->add('residence_postal_code')
            ->add('delivery_address')
            ->add('delivery_city')
            ->add('delivery_country')
            ->add('delivery_index')
            ->add('delivery_option')
            ->add('delivery_recipient')
            ->add('identity_citizenship')
            ->add('identity_type')
            ->add('identity_number')
            ->add('identity_issue_date')
            ->add('identity_expiry_date')
            ->add('identity_issuer')
            ->add('additional_politic_person')
            ->add('additional_politic_family')
            ->add('additional_promo_code')

            ->add('sent_to_getid')
            ->add('completed')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}
