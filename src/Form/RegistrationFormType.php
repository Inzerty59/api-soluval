<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('surname')
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => ['label' => 'Email'],
                'second_options' => ['label' => 'Confirmer l\'email'],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
            ])
            ->add('accountType', ChoiceType::class, [
                'choices' => [
                    'Particulier' => 'particulier',
                    'Professionnel' => 'professionnel',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Type de compte',
            ])
            ->add('companyName', null, ['label' => 'Nom de l\'entreprise', 'required' => false])
            ->add('siretNumber', null, ['label' => 'Numéro de SIRET', 'required' => false])
            ->add('vatNumber', null, ['label' => 'Numéro de TVA', 'required' => false])
        ;

        $builder->get('accountType')->addEventListener(
            \Symfony\Component\Form\FormEvents::POST_SUBMIT,
            function ($event) use ($builder) {
                $accountType = $event->getForm()->getData();
                $companyFields = ['companyName', 'siretNumber', 'vatNumber'];

                foreach ($companyFields as $field) {
                    $builder->get($field)->setRequired($accountType === 'professionnel');
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
