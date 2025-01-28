<?php

namespace App\Form;

use App\Entity\BillingAdress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BillingAdressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Firstname', TextType::class, [
                'attr' => ['placeholder' => 'Prénom'],
                'label' => false,
            ])
            ->add('Lastname', TextType::class, [
                'attr' => ['placeholder' => 'Nom'],
                'label' => false,
            ])
            ->add('Phone', TextType::class, [
                'attr' => ['placeholder' => 'Téléphone'],
                'label' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Le numéro de téléphone doit contenir uniquement des chiffres.',
                    ]),
                ],
            ])
            ->add('Street', TextType::class, [
                'attr' => ['placeholder' => 'Adresse'],
                'label' => false,
            ])
            ->add('StreetAdditionnal', TextType::class, [
                'attr' => ['placeholder' => 'Adresse Complémentaire'],
                'label' => false,
            ])
            ->add('PostCode', TextType::class, [
                'attr' => ['placeholder' => 'Code postal'],
                'label' => false,
            ])
            ->add('City', TextType::class, [
                'attr' => ['placeholder' => 'Ville'],
                'label' => false,
            ])
            ->add('CountryId', ChoiceType::class, [
                'choices' => [                    
                    'France' => 53,  
                    'Algérie' => 1084,  
                    'Allemagne' => 1058,  
                    'Autriche' => 1077,  
                    'Belgique' => 383,  
                    'Corse' => 1066,  
                    'Danemark' => 1081,  
                    'Espagne' => 997,  
                    'Finlande' => 1080,  
                    'Grèce' => 1082,  
                    'Guadeloupe' => 1063,  
                    'Guyane Française' => 1074,  
                    'Ile de la Réunion' => 1065,  
                    'Italie' => 998,  
                    'Luxembourg' => 1000,  
                    'Maroc' => 1083,  
                    'Martinique' => 1064,  
                    'Mayotte' => 1067,  
                    'Monaco' => 1075,  
                    'Norvège' => 1078,  
                    'Pays-Bas' => 1073,  
                    'Polynésie Française' => 1068,  
                    'Portugal' => 1001,  
                    'Saint-Barthélemy' => 1069,  
                    'Saint-Martin' => 1070,  
                    'Saint-Pierre-et-Miquelon' => 1071,  
                    'Suède' => 1079,  
                    'Suisse' => 1017,  
                    'Wallis et Futuna' => 1072,
                ],
                'attr' => ['placeholder' => 'Pays'],
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BillingAdress::class,
        ]);
    }
}