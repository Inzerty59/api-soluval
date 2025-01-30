<?php

namespace App\Form;

use App\Entity\DeliveryAdress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class DeliveryAdressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Firstname', TextType::class, [
                'attr' => ['placeholder' => 'Prénom'],
                'label' => false,
                'required' => false,
            ])
            ->add('Lastname', TextType::class, [
                'attr' => ['placeholder' => 'Nom'],
                'label' => false,
                'required' => false,
            ])
            ->add('Phone', TextType::class, [
                'attr' => ['placeholder' => 'Téléphone'],
                'label' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Le numéro de téléphone doit contenir uniquement des chiffres.',
                    ]),
                ],
            ])
            ->add('Street', TextType::class, [
                'attr' => ['placeholder' => 'Adresse'],
                'label' => false,
                'required' => false,
            ])
            ->add('StreetAdditionnal', TextType::class, [
                'attr' => ['placeholder' => 'Adresse Complémentaire'],
                'label' => false,
                'required' => false,
            ])
            ->add('PostCode', TextType::class, [
                'attr' => ['placeholder' => 'Code postal'],
                'label' => false,
                'required' => false,
            ])
            ->add('City', TextType::class, [
                'attr' => ['placeholder' => 'Ville'],
                'label' => false,
                'required' => false,
            ])
            ->add('CountryId', ChoiceType::class, [
                'choices' => [                    
                    'Algérie' => 32,  
                    'Allemagne' => 5,  
                    'Autriche' => 6,  
                    'Belgique' => 1,  
                    'Corse' => 206,  
                    'Danemark' => 9,  
                    'Espagne' => 3,  
                    'Finlande' => 11,  
                    'France' => 0,  
                    'Grèce' => 12,  
                    'Guadeloupe' => 68,  
                    'Guyane Française' => 96,  
                    'Ile de la Réunion' => 77,  
                    'Italie' => 4,  
                    'Luxembourg' => 17,  
                    'Maroc' => 31,  
                    'Martinique' => 69,  
                    'Mayotte' => 83,  
                    'Monaco' => 110,  
                    'Norvège' => 72,  
                    'Pays-Bas' => 19,  
                    'Polynésie Française' => 113,  
                    'Portugal' => 21,  
                    'Saint-Barthélemy' => 115,  
                    'Saint-Martin' => 117,  
                    'Saint-Pierre-et-Miquelon' => 119,  
                    'Suède' => 27,  
                    'Suisse' => 28,  
                    'Wallis et Futuna' => 120,
                ],
                'attr' => ['placeholder' => 'Pays'],
                'label' => false,
                'required' => false,
                'data' => 0,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DeliveryAdress::class,
        ]);
    }
}