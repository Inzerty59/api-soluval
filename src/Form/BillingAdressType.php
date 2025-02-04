<?php

namespace App\Form;

use App\Entity\BillingAdress;
use App\Entity\Shippings;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;

class BillingAdressType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $defaultShipping = $this->entityManager->getRepository(Shippings::class)->findOneBy(['Title' => 'France']);

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
                'attr' => ['placeholder' => 'Complément d\'adresse'],
                'label' => false,
                'required' => false,
            ])
            ->add('City', TextType::class, [
                'attr' => ['placeholder' => 'Ville'],
                'label' => false,
            ])
            ->add('PostCode', TextType::class, [
                'attr' => ['placeholder' => 'Code postal'],
                'label' => false,
            ])
            ->add('shipping', EntityType::class, [
                'class' => Shippings::class,
                'choice_label' => 'Title',
                'data' => $defaultShipping,
                'required' => true,
                'placeholder' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BillingAdress::class,
        ]);
    }
}