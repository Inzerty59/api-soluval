<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use App\Entity\User;
use App\Validator\Constraints\ValidSiret;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom ne doit pas être vide.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Za-zÀ-ÿ]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres.',
                    ]),
                ],
                'error_bubbling' => true,
            ])
            ->add('surname', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom ne doit pas être vide.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Za-zÀ-ÿ]+$/',
                        'message' => 'Le prénom ne doit contenir que des lettres.',
                    ]),
                ],
                'error_bubbling' => true,
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => ['label' => 'Email'],
                'second_options' => ['label' => 'Confirmer l\'email'],
                'invalid_message' => 'Les adresses email doivent correspondre.',
                'error_bubbling' => true,
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'constraints' => [
                        new Assert\NotBlank([
                            'message' => 'Le mot de passe ne doit pas être vide.',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                            'message' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.',
                        ]),
                    ],
                    'error_bubbling' => true,
                ],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'error_bubbling' => true,
            ])
            ->add('accountType', ChoiceType::class, [
                'choices' => [
                    'Particulier' => 'particulier',
                    'Professionnel' => 'professionnel',
                ],
                'label' => 'Type de compte',
                'error_bubbling' => true,
            ])
            ->add('companyName', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'constraints' => [
                    new Assert\Callback(function ($object, $context) {
                        if ($context->getRoot()->get('accountType')->getData() === 'professionnel' && empty($object)) {
                            $context->buildViolation('Le nom de l\'entreprise ne doit pas être vide.')
                                ->addViolation();
                        }
                    }),
                ],
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('siretNumber', TextType::class, [
                'label' => 'Numéro SIRET',
                'constraints' => [
                    new Assert\Callback(function ($object, $context) {
                        if ($context->getRoot()->get('accountType')->getData() === 'professionnel' && empty($object)) {
                            $context->buildViolation('Le numéro SIRET ne doit pas être vide.')
                                ->addViolation();
                        }
                    }),
                    new ValidSiret(),
                ],
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('vatNumber', TextType::class, [
                'label' => 'Numéro de TVA',
                'constraints' => [
                    new Assert\Callback(function ($object, $context) {
                        if ($context->getRoot()->get('accountType')->getData() === 'professionnel' && empty($object)) {
                            $context->buildViolation('Le numéro de TVA ne doit pas être vide.')
                                ->addViolation();
                        }
                    }),
                ],
                'required' => false,
                'error_bubbling' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}